<?php
$errors = $errors ?? [];
$success = $success ?? null;
$old = $old ?? [];
$activeTab = $activeTab ?? 'login';
$user = $user ?? null;

$oldValue = static function (string $key, string $default = '') use ($old): string {
    return e($old[$key] ?? $default);
};

$hasError = static function (string $key) use ($errors): bool {
    return array_key_exists($key, $errors);
};

$errorText = static function (string $key) use ($errors): string {
    return $errors[$key] ?? '';
};

$roleValue = $old['role'] ?? 'estudiante';
$roleValue = in_array($roleValue, ['estudiante', 'director'], true) ? $roleValue : 'estudiante';
?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestor de Titulacion - Acceso</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      color-scheme: light dark;
    }

    html {
      font-family: Inter, system-ui, ui-sans-serif;
    }

    .scrollbar-invisible {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }

    .scrollbar-invisible::-webkit-scrollbar {
      width: 0;
      height: 0;
    }

    .fade-enter {
      opacity: 0;
      transform: translateY(6px);
    }

    .fade-enter-active {
      opacity: 1;
      transform: none;
      transition: opacity 0.18s ease, transform 0.18s ease;
    }

    /* Ajuste para pantallas muy pequenas */
    @media (max-width: 375px) {
      .text-4xl-mobile {
        font-size: 2.25rem;
        line-height: 2.5rem;
      }
    }

    /* Ajuste para tablets en modo vertical */
    @media (min-width: 768px) and (max-width: 1023px) and (orientation: portrait) {
      .tablet-portrait-stack {
        flex-direction: column;
      }
    }
  </style>
</head>
<body class="min-h-full bg-slate-50">
  <div class="min-h-screen lg:flex tablet-portrait-stack">
    <aside class="lg:w-1/2 lg:h-screen relative flex items-center justify-center px-4 sm:px-8 md:px-12 lg:px-16 xl:px-20 py-8 sm:py-12 lg:py-16 bg-[#1E3A8A] text-white lg:flex-shrink-0">
      <div class="max-w-lg w-full">
        <div class="mb-8 sm:mb-12 lg:mb-16 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 lg:gap-10 opacity-95">
          <div class="h-12 sm:h-14 lg:h-16 flex items-center">
            <img src="<?= e(url('/assets/logo_tecnm.svg')); ?>" alt="Logo TECNM" class="h-full w-auto object-contain text-white" />
          </div>
          <div class="h-12 sm:h-14 lg:h-16 flex items-center">
            <img src="<?= e(url('/assets/logo_tec_merida.svg')); ?>" alt="Logo TEC Merida" class="h-full w-auto object-contain text-white" />
          </div>
          <div class="h-12 sm:h-14 lg:h-16 flex items-center">
            <img src="<?= e(url('/assets/logo_sistemas.svg')); ?>" alt="Logo Sistemas" class="h-full w-auto object-contain text-white" />
          </div>
        </div>

        <div class="text-center lg:text-left">
          <p class="text-3xl sm:text-4xl lg:text-5xl text-4xl-mobile font-extrabold leading-tight">
            Hola,<br /><span class="font-extrabold">bienvenido</span>
          </p>
          <p class="mt-4 sm:mt-6 max-w-md mx-auto lg:mx-0 text-white/80 text-base sm:text-lg">
            Inicia sesion o crea una cuenta para ingresar al sistema.
          </p>
        </div>
      </div>

      <div class="hidden lg:block absolute right-0 top-0 h-full w-px bg-white/20"></div>
    </aside>

    <main class="lg:w-1/2 lg:h-screen lg:overflow-y-auto flex items-center justify-center px-4 sm:px-6 md:px-8 lg:px-12 xl:px-16 py-6 sm:py-8 lg:py-16">
      <div class="w-full max-w-md">
        <?php if ($user): ?>
          <div class="mb-5 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <p class="text-sm text-slate-500">Sesion activa</p>
            <div class="mt-1 text-lg font-semibold text-slate-800"><?= e($user['full_name']); ?></div>
            <div class="text-sm text-slate-500"><?= e(strtolower($user['email'])); ?> &bull; <?= e(ucfirst($user['role'])); ?></div>
            <form class="mt-4" method="post" action="<?= e(url('/logout')); ?>">
              <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                Cerrar sesion
              </button>
            </form>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            <?= e($success); ?>
          </div>
        <?php endif; ?>

        <div class="mb-6 lg:mb-8">
          <div id="auth-tabs" role="tablist" aria-label="Cambiar vista" data-active-tab="<?= e($activeTab); ?>" class="w-full rounded-2xl bg-slate-200 p-1 grid grid-cols-2">
            <button id="tab-login" type="button" role="tab" aria-selected="<?= $activeTab === 'login' ? 'true' : 'false'; ?>" aria-controls="panel-login" class="tab rounded-xl px-4 sm:px-6 lg:px-8 py-3 text-sm font-medium text-slate-700 transition <?= $activeTab === 'login' ? 'bg-white shadow-sm' : ''; ?>">
              Iniciar sesion
            </button>
            <button id="tab-register" type="button" role="tab" aria-selected="<?= $activeTab === 'register' ? 'true' : 'false'; ?>" aria-controls="panel-register" class="tab rounded-xl px-4 sm:px-6 lg:px-8 py-3 text-sm font-medium text-slate-700 transition <?= $activeTab === 'register' ? 'bg-white shadow-sm' : ''; ?>">
              Crear cuenta
            </button>
          </div>
        </div>

        <div id="panel-container" class="relative overflow-y-auto scrollbar-invisible transition-[height] duration-300 ease-out max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-180px)] lg:max-h-[70vh]">
          <section id="panel-login" class="panel <?= $activeTab === 'register' ? 'hidden' : ''; ?>" role="tabpanel" aria-hidden="<?= $activeTab === 'register' ? 'true' : 'false'; ?>">
            <div class="rounded-2xl bg-white p-4 sm:p-6 lg:p-8 shadow-sm ring-1 ring-slate-200">
              <form class="space-y-4 sm:space-y-5" method="post" action="<?= e(url('/login')); ?>">
                <?php if ($hasError('login_general')): ?>
                  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <?= e($errorText('login_general')); ?>
                  </div>
                <?php endif; ?>

                <div>
                  <label for="login-email" class="mb-2 block text-sm font-semibold text-slate-700">Correo institucional</label>
                  <input id="login-email" type="email" name="email" required value="<?= $oldValue('login_email'); ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('login_email') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="correo@itmerida.edu.mx" autocomplete="email" />
                  <?php if ($hasError('login_email')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('login_email')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <div class="mb-2 flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Contrasena</label>
                  </div>
                  <div class="relative">
                    <input id="password" type="password" name="password" required class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('login_password') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Tu contrasena" autocomplete="current-password" />
                    <button type="button" id="togglePassBtn" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600" aria-label="Mostrar contrasena">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                  <?php if ($hasError('login_password')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('login_password')); ?></p>
                  <?php endif; ?>
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1B307C] focus:outline-none focus:ring-2 focus:ring-indigo-200">
                  Ingresar
                </button>
              </form>
            </div>
          </section>

          <section id="panel-register" class="panel <?= $activeTab === 'register' ? '' : 'hidden'; ?>" role="tabpanel" aria-hidden="<?= $activeTab === 'register' ? 'false' : 'true'; ?>">
            <div class="rounded-2xl bg-white p-4 sm:p-6 lg:p-8 shadow-sm ring-1 ring-slate-200">
              <form class="space-y-4 sm:space-y-5" method="post" action="<?= e(url('/register')); ?>">
                <?php if ($hasError('register_general')): ?>
                  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    <?= e($errorText('register_general')); ?>
                  </div>
                <?php endif; ?>

                <div>
                  <label for="full_name" class="mb-2 block text-sm font-semibold text-slate-700">Nombre completo</label>
                  <input id="full_name" type="text" name="full_name" required value="<?= $oldValue('full_name'); ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('full_name') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Nombre y apellidos" autocomplete="name" />
                  <?php if ($hasError('full_name')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('full_name')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="register-email" class="mb-2 block text-sm font-semibold text-slate-700">Correo institucional</label>
                  <input id="register-email" type="email" name="email" required value="<?= $oldValue('email'); ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('email') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="correo@itmerida.edu.mx" autocomplete="email" />
                  <?php if ($hasError('email')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('email')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">Rol en el sistema</label>
                  <select id="role" name="role" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-200">
                    <option value="estudiante" <?= $roleValue === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                    <option value="director" <?= $roleValue === 'director' ? 'selected' : ''; ?>>Director</option>
                  </select>
                </div>

                <div id="field-matricula" class="space-y-2 <?= $roleValue === 'director' ? 'hidden' : ''; ?>">
                  <label for="matricula" class="block text-sm font-semibold text-slate-700">Matricula</label>
                  <input id="matricula" type="text" name="matricula" value="<?= $oldValue('matricula'); ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('matricula') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Ej. B12345" />
                  <?php if ($hasError('matricula')): ?>
                    <p class="mt-1 text-xs font-medium text-red-600"><?= e($errorText('matricula')); ?></p>
                  <?php endif; ?>
                </div>

                <div id="field-depto" class="space-y-2 <?= $roleValue === 'director' ? '' : 'hidden'; ?>">
                  <label for="department" class="block text-sm font-semibold text-slate-700">Departamento</label>
                  <input id="department" type="text" name="department" value="<?= $oldValue('department'); ?>" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('department') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Ej. Ciencias Basicas" />
                  <?php if ($hasError('department')): ?>
                    <p class="mt-1 text-xs font-medium text-red-600"><?= e($errorText('department')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="register-password" class="mb-2 block text-sm font-semibold text-slate-700">Contrasena</label>
                  <div class="relative">
                    <input id="register-password" type="password" name="password" required class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('password') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Minimo 8 caracteres" autocomplete="new-password" />
                    <button type="button" class="toggle-eye absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600" data-toggle-for="register-password" aria-label="Mostrar contrasena">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                  <?php if ($hasError('password')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('password')); ?></p>
                  <?php endif; ?>
                </div>

                <div>
                  <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contrasena</label>
                  <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 <?= $hasError('password_confirmation') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'focus:border-indigo-500 focus:ring-indigo-200'; ?>" placeholder="Repite la contrasena" autocomplete="new-password" />
                    <button type="button" class="toggle-eye absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600" data-toggle-for="password_confirmation" aria-label="Mostrar contrasena">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                  <?php if ($hasError('password_confirmation')): ?>
                    <p class="mt-2 text-xs font-medium text-red-600"><?= e($errorText('password_confirmation')); ?></p>
                  <?php endif; ?>
                </div>

                <button type="submit" class="w-full rounded-xl bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1B307C] focus:outline-none focus:ring-2 focus:ring-indigo-200">
                  Crear cuenta
                </button>
              </form>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <script>
    const tabButtons = Array.from(document.querySelectorAll('#auth-tabs .tab'));
    const panels = Array.from(document.querySelectorAll('.panel'));
    const authTabs = document.getElementById('auth-tabs');

    function setContainerHeightFor(panel) {
      const container = document.getElementById('panel-container');
      if (!panel || !container) return;
      const clone = panel.cloneNode(true);
      clone.style.height = 'auto';
      clone.classList.remove('hidden');
      clone.style.position = 'absolute';
      clone.style.visibility = 'hidden';
      container.appendChild(clone);
      const height = clone.getBoundingClientRect().height;
      container.style.height = height + 'px';
      container.removeChild(clone);
    }

    function activateTab(button) {
      const targetId = button.getAttribute('aria-controls');
      const nextPanel = document.getElementById(targetId);
      if (!nextPanel) return;

      tabButtons.forEach((btn) => {
        btn.classList.remove('bg-white', 'shadow-sm');
        btn.setAttribute('aria-selected', 'false');
      });

      panels.forEach((panel) => {
        panel.classList.add('hidden');
        panel.setAttribute('aria-hidden', 'true');
      });

      button.classList.add('bg-white', 'shadow-sm');
      button.setAttribute('aria-selected', 'true');

      nextPanel.classList.remove('hidden');
      nextPanel.setAttribute('aria-hidden', 'false');
      nextPanel.classList.add('fade-enter');
      requestAnimationFrame(() => nextPanel.classList.add('fade-enter-active'));
      setTimeout(() => nextPanel.classList.remove('fade-enter', 'fade-enter-active'), 200);
      setContainerHeightFor(nextPanel);
    }

    window.addEventListener('load', () => {
      const requestedTab = authTabs.dataset.activeTab || 'login';
      const defaultButton = requestedTab === 'register' ? document.getElementById('tab-register') : document.getElementById('tab-login');
      if (defaultButton) {
        activateTab(defaultButton);
        defaultButton.focus({ preventScroll: true });
      }
    });

    tabButtons.forEach((btn) => btn.addEventListener('click', () => activateTab(btn)));

    authTabs.addEventListener('keydown', (event) => {
      if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
      event.preventDefault();
      const index = tabButtons.indexOf(document.activeElement);
      if (index === -1) return;
      const nextIndex = event.key === 'ArrowRight' ? (index + 1) % tabButtons.length : (index - 1 + tabButtons.length) % tabButtons.length;
      tabButtons[nextIndex].focus();
      activateTab(tabButtons[nextIndex]);
    });

    function eyeSvg(open = true) {
      return open
        ? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
        : '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.8 21.8 0 0 1 5.06-6.06"></path><path d="M1 1l22 22"></path><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.73 21.73 0 0 1-3.17 4.26"></path><path d="M12 12a3 3 0 0 0-3-3"></path></svg>';
    }

    const togglePassBtn = document.getElementById('togglePassBtn');
    const passInput = document.getElementById('password');
    if (togglePassBtn && passInput) {
      let open = false;
      togglePassBtn.addEventListener('click', () => {
        open = !open;
        passInput.type = open ? 'text' : 'password';
        togglePassBtn.innerHTML = eyeSvg(open);
        togglePassBtn.setAttribute('aria-label', open ? 'Ocultar contrasena' : 'Mostrar contrasena');
      });
    }

    document.querySelectorAll('.toggle-eye').forEach((btn) => {
      const input = document.getElementById(btn.dataset.toggleFor);
      if (!input) return;
      let open = false;
      btn.addEventListener('click', () => {
        open = !open;
        input.type = open ? 'text' : 'password';
        btn.innerHTML = eyeSvg(open);
        btn.setAttribute('aria-label', open ? 'Ocultar contrasena' : 'Mostrar contrasena');
      });
    });

    const roleSelect = document.getElementById('role');
    const fieldMat = document.getElementById('field-matricula');
    const fieldDept = document.getElementById('field-depto');

    function syncRoleFields() {
      if (!roleSelect) return;
      const isDirector = roleSelect.value === 'director';
      if (fieldMat && fieldDept) {
        if (isDirector) {
          fieldMat.classList.add('hidden');
          fieldDept.classList.remove('hidden');
        } else {
          fieldDept.classList.add('hidden');
          fieldMat.classList.remove('hidden');
        }
        setTimeout(() => {
          const activePanel = document.querySelector('.panel:not(.hidden)');
          setContainerHeightFor(activePanel);
        }, 150);
      }
    }

    if (roleSelect) {
      roleSelect.addEventListener('change', syncRoleFields);
      syncRoleFields();
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const active = document.querySelector('.panel:not(.hidden)');
        if (active) setContainerHeightFor(active);
      }, 150);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Tab') {
        document.body.classList.add('using-keyboard');
      }
    });

    document.addEventListener('mousedown', () => {
      document.body.classList.remove('using-keyboard');
    });
  </script>

  <style>
    .using-keyboard *:focus {
      outline: 2px solid #1E3A8A !important;
      outline-offset: 2px !important;
    }

    input:focus,
    select:focus,
    button:focus {
      box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.2);
    }
  </style>
</body>
</html>
