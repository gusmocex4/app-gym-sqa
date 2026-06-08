# Azure Deployment Guide

This project now targets Azure App Service with Azure Cosmos DB for MongoDB.

## Azure Resources to Deploy

- Resource Group
- Azure Container Registry
- App Service Plan for Linux
- Azure Web App for Containers
- Azure Cosmos DB for MongoDB
- Azure Key Vault
- Application Insights

## Image Layout

Only one image needs to be built and pushed to ACR:

- App image from [Dockerfile](/Users/unidentified/Downloads/app-gym-main/Dockerfile:1)

Do not push a database image. Cosmos DB is a managed Azure service.

Files added for this flow:

- [docker-compose.azure.yml](/Users/unidentified/Downloads/app-gym-main/docker-compose.azure.yml:1)
- [azure/acr-build.sh](/Users/unidentified/Downloads/app-gym-main/azure/acr-build.sh:1)
- [azure/appsettings.example.env](/Users/unidentified/Downloads/app-gym-main/azure/appsettings.example.env:1)

## App Settings for App Service

Set these App Service settings:

- `WEBSITES_PORT=80`
- `MONGODB_URI=@Microsoft.KeyVault(VaultName=<vault-name>;SecretName=mongodb-uri)`
- `MONGODB_DATABASE=appgym`
- `MONGODB_USERS_COLLECTION=usuarios`

## What to Keep in Key Vault

Keep these as Key Vault secrets:

- `mongodb-uri`

The application only requires a MongoDB connection string secret right now. Database and collection names are configuration, not secrets, so they can stay in App Service settings unless you want to centralize everything.

If you choose to split the connection string yourself instead of storing one URI, keep these in Key Vault and build `MONGODB_URI` from them during deployment:

- `cosmos-mongo-username`
- `cosmos-mongo-password`
- `cosmos-mongo-host`

For this app, a single `mongodb-uri` secret is simpler and less error-prone.

## Expected Cosmos DB Data Shape

Users are now stored as MongoDB documents. A seed file is included at [database.mongodb.json](/Users/unidentified/Downloads/app-gym-main/database.mongodb.json:1).

Example document:

```json
{
  "_id": "admin-user-000000000001",
  "nombre": "Admin",
  "apellido": "User",
  "email": "admin@example.com",
  "password": "<bcrypt-hash>",
  "token": "",
  "admin": 1
}
```

## Container Build and Push

Build in ACR:

```bash
./azure/acr-build.sh <resource-group> <acr-name> appgym-app <tag>
```

This performs a remote build from the repo and pushes the app image into ACR.

## App Service Deployment Flow

1. Create the ACR.
2. Run the ACR build script to publish the app image.
3. Create the Cosmos DB for MongoDB account, database, and `usuarios` collection.
4. Import [database.mongodb.json](/Users/unidentified/Downloads/app-gym-main/database.mongodb.json:1) into the `usuarios` collection.
5. Create the Web App for Containers on Linux.
6. Point the Web App to the ACR image.
7. Enable a managed identity on the Web App.
8. Grant the Web App permission to read Key Vault secrets.
9. Add the App Service settings listed above.
10. Restart the app and validate login and registration flows.

## Notes on Key Vault References

Azure App Service can use Key Vault references directly in app settings. Microsoft documents that pattern here:

- [Use Key Vault references as app settings in Azure App Service](https://learn.microsoft.com/en-us/azure/app-service/app-service-key-vault-references)

## Notes on Platform Choice

This repo now uses the MongoDB PHP extension and a document-based persistence layer for users. That is why Cosmos DB for MongoDB is the correct Azure database option for this codebase, rather than Azure Database for MySQL.
