const { test, expect } = require('@playwright/test');

async function login(page, email, password) {
  await page.goto('/login');
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('#submit_button').click();
}

test.describe('FitZone authentication test plan', () => {
  test('CP-01 rejects empty login fields', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('Todos los campos son obligatorios');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-02 rejects an unknown user', async ({ page }) => {
    await login(page, 'noexiste@test.com', '12345678');

    await expect(page.locator('.alerta.error')).toContainText('Usuario no encontrado');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-03 rejects an incorrect password', async ({ page }) => {
    await login(page, 'user@example.com', 'claveerrada');

    await expect(page.locator('.alerta.error')).toContainText('Password errada');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-04 logs in a normal user and redirects to the user home', async ({ page }) => {
    await login(page, 'user@example.com', '123456');

    await expect(page).toHaveURL(/\/inicio-user$/);
  });

  test('CP-05 logs in an admin user and redirects to the admin home', async ({ page }) => {
    await login(page, 'admin@example.com', '123456');

    await expect(page).toHaveURL(/\/inicio-admin$/);
  });

  test('CP-06 rejects empty registration fields', async ({ page }) => {
    await page.goto('/crear-cuenta');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('Todos los campos son obligatorios');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-07 rejects a short password during registration', async ({ page }) => {
    await page.goto('/crear-cuenta');
    await page.locator('#nombre').fill('Test');
    await page.locator('#apellido').fill('User');
    await page.locator('#email').fill('short-password@example.com');
    await page.locator('#password').fill('1234567');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('La contraseña debe tener al menos 8 caracteres');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-08 rejects duplicate email registration', async ({ page }) => {
    await page.goto('/crear-cuenta');
    await page.locator('#nombre').fill('Normal');
    await page.locator('#apellido').fill('User');
    await page.locator('#email').fill('user@example.com');
    await page.locator('#password').fill('12345678');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('El usuario ya está registrado');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-09 registers a valid new user and redirects to login', async ({ page }) => {
    const email = `new-user-${Date.now()}@example.com`;

    await page.goto('/crear-cuenta');
    await page.locator('#nombre').fill('New');
    await page.locator('#apellido').fill('User');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('12345678');
    await page.locator('#submit_button').click();

    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-10 logs out and redirects to the landing page', async ({ page }) => {
    await login(page, 'user@example.com', '123456');
    await expect(page).toHaveURL(/\/inicio-user$/);

    await page.goto('/logout');
    await expect(page).toHaveURL(/\/$/);
  });

  test('CP-11 keeps the session active across expected navigation', async ({ page }) => {
    await login(page, 'user@example.com', '123456');
    await page.goto('/inicio-user');

    await expect(page).toHaveURL(/\/inicio-user$/);
  });

  test('CP-12 serves the main public routes', async ({ page }) => {
    for (const route of ['/', '/login', '/crear-cuenta']) {
      const response = await page.goto(route);
      expect(response && response.ok()).toBeTruthy();
    }
  });
});
