const { test, expect } = require('@playwright/test');

const datosPrueba = {
  usuarioRegistrado: {
    email: 'user@example.com',
    password: '123456'
  },
  rutasPublicas: ['/', '/login', '/crear-cuenta']
};

async function iniciarSesion(page) {
  await page.goto('/login');
  await page.locator('#email').fill(datosPrueba.usuarioRegistrado.email);
  await page.locator('#password').fill(datosPrueba.usuarioRegistrado.password);
  await page.locator('#submit_button').click();
}

test.describe('SCRUM-149 - Pruebas de sesion', () => {
  test('CP-FZ-014 cierra sesion y redirige a pagina principal', async ({ page }) => {
    await iniciarSesion(page);
    await expect(page).toHaveURL(/\/inicio-user$/);

    await page.goto('/logout');

    await expect(page).toHaveURL(/\/$/);
  });

  test('CP-FZ-015 mantiene sesion activa durante navegacion esperada', async ({ page }) => {
    await iniciarSesion(page);
    await page.goto('/inicio-user');

    await expect(page).toHaveURL(/\/inicio-user$/);
  });

  test('CP-FZ-016 sirve rutas publicas principales sin autenticacion', async ({ page }) => {
    for (const ruta of datosPrueba.rutasPublicas) {
      const respuesta = await page.goto(ruta);

      expect(respuesta && respuesta.ok()).toBeTruthy();
    }
  });
});
