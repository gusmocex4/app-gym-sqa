const { test, expect } = require('@playwright/test');

const datosPrueba = {
  usuarioRegistrado: {
    email: 'user@example.com',
    password: '123456'
  },
  administradorRegistrado: {
    email: 'admin@example.com',
    password: '123456'
  }
};

async function iniciarSesion(page, email, password) {
  await page.goto('/login');
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await page.locator('#submit_button').click();
}

test.describe('SCRUM-148 - Pruebas de control de acceso', () => {
  test('CP-FZ-010 bloquea acceso sin sesion a inicio de usuario', async ({ page }) => {
    await page.goto('/inicio-user');

    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-FZ-011 bloquea acceso sin sesion a inicio de administrador', async ({ page }) => {
    await page.goto('/inicio-admin');

    await expect(page).toHaveURL(/\/login$/);
  });

  test('CP-FZ-012 impide que usuario normal acceda al inicio de administrador', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.usuarioRegistrado.email,
      datosPrueba.usuarioRegistrado.password
    );

    await page.goto('/inicio-admin');

    await expect(page).not.toHaveURL(/\/inicio-admin$/);
  });

  test('CP-FZ-013 permite que administrador acceda al inicio de administrador', async ({ page }) => {
    await iniciarSesion(
      page,
      datosPrueba.administradorRegistrado.email,
      datosPrueba.administradorRegistrado.password
    );

    await expect(page).toHaveURL(/\/inicio-admin$/);
  });
});
