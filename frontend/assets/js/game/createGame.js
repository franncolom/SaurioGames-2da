let userLoggedIn = true;

document.addEventListener("DOMContentLoaded", () => {
  // Verificamos si ya hay una partida activa
  const partidaActiva = JSON.parse(localStorage.getItem("partidaActual"));
  if (partidaActiva && partidaActiva.estado === "iniciada") {
    alert("Ya hay una partida en curso. Continuá desde donde estabas.");
    window.location.href = "pages/game.html";
  }

  const playBtn = document.getElementById("playBtn");
  const playWindow = document.getElementById("playWindow");
  const gameForm = document.getElementById("gameForm");

  // Abrir el diálogo al hacer click en Jugar
  playBtn.addEventListener("click", () => {
    playWindow.showModal();
  });

  // Cerrar el diálogo al enviar el formulario y crear la partida
  gameForm.addEventListener("submit", (e) => {
    e.preventDefault();

    if (!userLoggedIn) {
      toggleWindow("loginWindow");
      return;
    }

    const cantJugadores = parseInt(
      document.getElementById("cantJugadores").value
    );

    const partida = {
      id: Date.now(),
      jugadores: cantJugadores,
      estado: "iniciada",
      turnoActual: 1,
      fecha: new Date(),
    };

    console.log("Partida creada:", partida);

    // Guardamos la partida para la siguiente página
    localStorage.setItem("partidaActual", JSON.stringify(partida));

    // Cerramos el diálogo
    playWindow.close();

    // Redireccionamos a game.html
    window.location.href = "pages/game.html";
  });
});
