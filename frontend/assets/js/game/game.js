// Partida
let partida = JSON.parse(localStorage.getItem("partidaActual"));
if (partida && partida.estado === "iniciada") {
  window.addEventListener("beforeunload", (e) => {
    e.preventDefault();
    e.returnValue = "Si recargas o cerras la página, se perderán los datos de la partida.";
  });
}

const elts = {
  text1: document.getElementById("text1"),
  text2: document.getElementById("text2"),
  container: document.getElementById("container")
};
const dadoContainer = document.querySelector(".dado_container");
const rollBtn = document.querySelector(".roll");
const dice = document.querySelector(".dado");
const mapa = document.querySelector(".mapa_container");
const exitBtn = document.getElementById("exitGame");

document.addEventListener("DOMContentLoaded", () => {
  const exitBtn = document.getElementById("exitGame");

  if (exitBtn) {
    exitBtn.addEventListener("click", () => {
      console.log("Botón salir clickeado"); // <-- prueba en consola
      localStorage.clear();
      window.location.href = "../index.html"; 
    });
  } else {
    console.error("No encontré el botón #exitGame");
  }
});


const texts = [
  "Preparando tablero...",
  "¡Iniciando partida!",
  "Jugador 1",
  "prepárate…",
  "¡TIRA EL DADO!"
];

const morphTime = 1;
const cooldownTime = 0.35;
let textIndex = 0;

elts.text1.style.opacity = "1";
elts.text2.style.opacity = "0";
elts.text1.textContent = texts[textIndex];
elts.text2.textContent = texts[textIndex + 1] || "";


function mostrarMapa() {
  // Ocultar textos
  if (elts.container) {
    elts.container.classList.add("fade-out");
    setTimeout(() => {
      elts.container.style.display = "none";
    }, 800);
  }

  // Mostrar el mapa
  if (mapa) {
    mapa.style.display = "block";
    mapa.classList.add("fade-in");
  }

  // Ocultar dado
  dadoContainer.style.display = "none";

  // Rehabilitar otras cosas si necesitas
}

// Animación de textos con morph
function setMorph(fraction) {
  fraction = Math.max(fraction, 0.0001);
  elts.text2.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
  elts.text2.style.opacity = `${Math.pow(fraction, 0.4)}`;
  fraction = 1 - fraction;
  elts.text1.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
  elts.text1.style.opacity = `${Math.pow(fraction, 0.4)}`;
  elts.text1.textContent = texts[textIndex];
  elts.text2.textContent = texts[textIndex + 1] || "";
}

function showNextText() {
  if (textIndex >= texts.length - 1) {
    elts.container.classList.add("fade-out");
    dadoContainer.style.display = "flex";
    return;
  }

  let start = null;

  function step(timestamp) {
    if (!start) start = timestamp;
    const elapsed = (timestamp - start) / 1000;
    let fraction = Math.min(elapsed / morphTime, 1);
    setMorph(fraction);

    if (fraction < 1) {
      requestAnimationFrame(step);
    } else {
      textIndex++;
      setTimeout(showNextText, cooldownTime * 1000);
    }
  }

  requestAnimationFrame(step);
}

showNextText();

const rollDice = (random) => {
  dice.style.animation = 'rolling 1s';

  setTimeout(() => {
    switch (random) {
      case 1: dice.style.transform = 'rotateX(0deg) rotateY(0deg)'; break;
      case 2: dice.style.transform = 'rotateX(-90deg) rotateY(0deg)'; break;
      case 3: dice.style.transform = 'rotateX(0deg) rotateY(90deg)'; break;
      case 4: dice.style.transform = 'rotateX(0deg) rotateY(-90deg)'; break;
      case 5: dice.style.transform = 'rotateX(90deg) rotateY(0deg)'; break;
      case 6: dice.style.transform = 'rotateX(180deg) rotateY(0deg)'; break;
    }
    dice.style.animation = 'none';

    // Guardar resultado en partida
    partida.ultimoDado = random;
    localStorage.setItem("partidaActual", JSON.stringify(partida));

    // Desactivar botón después de tirar
    rollBtn.disabled = true;

    // Aquí podes llamar función para mostrar el mapa
    mostrarMapa(); 
  }, 1000);
};

const randomDice = () => {
  const random = Math.floor(Math.random() * 6) + 1;
  rollDice(random);
};

let tiroRealizado = false;
rollBtn.addEventListener('click', () => {
  if (!tiroRealizado) {
    randomDice();
    tiroRealizado = true;
  } else {
    alert("Ya tiraste el dado en este turno.");
  }
});
