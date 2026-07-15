const { test, expect } = require('@playwright/test');

const datosPrueba = {
  usuarioNoRegistrado: {
    email: 'noexiste@test.com',
    password: '12345678'
  },
  usuarioRegistrado: {
    email: 'user@example.com',
    password: '123456',
    passwordIncorrecta: 'claveerrada',
    rutaEsperada: /\/inicio-user$/
  },
  administradorRegistrado: {
    email: 'admin@example.com',
    password: '123456',
    rutaEsperada: /\/inicio-admin$/
  }
};

async function iniciarSesion(page, email, password) {
  await page.goto('/login');
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('#submit_button').click();
}

test.describe('SCRUM-146 - Pruebas de autenticacion', () => {
  test('CP-FZ-001 rechaza login con campos obligatorios vacios', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('Todos los campos son obligatorios');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-FZ-002 rechaza login con usuario no registrado', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.usuarioNoRegistrado.email,
      datosPrueba.usuarioNoRegistrado.password
    );

    await expect(page.locator('.alerta.error')).toContainText('Usuario no encontrado');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-FZ-003 rechaza login con password incorrecta', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.usuarioRegistrado.email,
      datosPrueba.usuarioRegistrado.passwordIncorrecta
    );

    await expect(page.locator('.alerta.error')).toContainText('Password errada');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-FZ-004 autentica usuario normal y redirige a inicio de usuario', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.usuarioRegistrado.email,
      datosPrueba.usuarioRegistrado.password
    );

    await expect(page).toHaveURL(datosPrueba.usuarioRegistrado.rutaEsperada);
  });

  test('CP-FZ-005 autentica administrador y redirige a inicio de administrador', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.administradorRegistrado.email,
      datosPrueba.administradorRegistrado.password
    );

    await expect(page).toHaveURL(datosPrueba.administradorRegistrado.rutaEsperada);
  });
});
