const { test, expect } = require('@playwright/test');

const datosPrueba = {
  usuarioIncompleto: {
    nombre: '',
    apellido: '',
    email: '',
    password: ''
  },
  passwordCorta: {
    nombre: 'Test',
    apellido: 'User',
    email: 'short-password@example.com',
    password: '1234567'
  },
  correoDuplicado: {
    nombre: 'Normal',
    apellido: 'User',
    email: 'user@example.com',
    password: '12345678'
  },
  usuarioValido: {
    nombre: 'Nuevo',
    apellido: 'Usuario',
    password: '12345678'
  }
};

async function completarRegistro(page, usuario) {
  await page.goto('/crear-cuenta');
  await page.locator('#nombre').fill(usuario.nombre);
  await page.locator('#apellido').fill(usuario.apellido);
  await page.locator('#email').fill(usuario.email);
  await page.locator('#password').fill(usuario.password);
  await page.locator('#submit_button').click();
}

test.describe('SCRUM-147 - Pruebas de registro', () => {
  test('CP-FZ-006 rechaza registro con campos obligatorios vacios', async ({ page }) => {
    await page.goto('/crear-cuenta');
    await page.locator('#submit_button').click();

    await expect(page.locator('.alerta.error')).toContainText('Todos los campos son obligatorios');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-FZ-007 rechaza registro con password menor a ocho caracteres', async ({ page }) => {
    await completarRegistro(page, datosPrueba.passwordCorta);

    await expect(page.locator('.alerta.error')).toContainText('La contraseña debe tener al menos 8 caracteres');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-FZ-008 rechaza registro con correo ya existente', async ({ page }) => {
    await completarRegistro(page, datosPrueba.correoDuplicado);

    await expect(page.locator('.alerta.error')).toContainText('El usuario ya está registrado');
    await expect(page).toHaveURL(/\/crear-cuenta$/);
  });

  test('CP-FZ-009 registra usuario valido y redirige a login', async ({ page }) => {
    const emailUnico = `nuevo-usuario-${Date.now()}@example.com`;

    await completarRegistro(page, {
      ...datosPrueba.usuarioValido,
      email: emailUnico
    });

    await expect(page).toHaveURL(/\/login$/);
  });
});
