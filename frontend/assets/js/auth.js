async function login(nombre_usuario, contraseña) {
  try {
    // Query params:
    // - entity=usuarios: indica que la acción se refiere al recurso "usuarios" en el backend
    // - action=login: solicita la acción de inicio de sesión (POST con nombre_usuario y contraseña)
    const response = await fetch('/api/index.php?entity=usuarios&action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre_usuario, contraseña })
    });

    const text = await response.text();
    let data;
    try { data = text ? JSON.parse(text) : {}; } catch { data = { message: 'Respuesta no es JSON', raw: text }; }

    if (response.ok) {
      // Guardar usuario y actualizar UI
      localStorage.setItem('usuario', JSON.stringify(data));
      updateProfileNameFromData(data);
      const dialog = document.getElementById('loginWindow');
      if (dialog && typeof dialog.close === 'function') dialog.close();
      console.log('Usuario logueado:', data);
    } else {
      console.error('Error:', data.error || data.message || data);
    }
  } catch (err) {
    console.error('Fallo de red:', err);
  }
}

function updateProfileNameFromData(data) {
  const nombre = data && data.nombre_usuario ? data.nombre_usuario : null;
  if (!nombre) return;
  // Actualiza todos los lugares donde se muestra el nombre
  document.querySelectorAll('.profile_name').forEach(el => {
    el.textContent = nombre;
  });
}

// Inicializar nombre desde localStorage al cargar
(function initUserFromStorage() {
  try {
    const stored = localStorage.getItem('usuario');
    if (!stored) return;
    const usuario = JSON.parse(stored);
    updateProfileNameFromData(usuario);
  } catch {}
})();

