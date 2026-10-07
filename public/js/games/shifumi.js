const victories = document.getElementById("victories");
const defeats = document.getElementById("defeats");
const nulls = document.getElementById("nulls");
const playButtons = document.getElementById("play-buttons");
const resetButton = document.getElementById("reset");
const result = document.getElementById("shifumi-result");

const moves = {
    rock: 0,
    paper: 1,
    scissors: 2
};

const state = {
    victories: 0,
    defeats: 0,
    nulls: 0
};

function randomMove() {
    return Math.floor(Math.random() * 3);
}

function getResult(player, robot) {
    return (player - robot + 3) % 3;
}

function show() {
    victories.textContent = state.victories;
    defeats.textContent = state.defeats;
    nulls.textContent = state.nulls;
}

function reset() {
    state.victories = 0;
    state.defeats = 0;
    state.nulls = 0;
    result.textContent = "Welcome to ShiFuMi :)";
    show();
}

function play(player) {
    const robot = randomMove();
    const res = getResult(player, robot);

    console.log(`player chose ${player} and bot chose ${robot} result is ${res}`);
    
    if (res === 0) {
        state.nulls++;
        result.textContent = "Null";
    } else if (res === 1) {
        state.victories++;
        result.textContent = "Player won";
    } else {
        state.defeats++;
        result.textContent = "Bot won";
    }
    show();
}

playButtons.addEventListener("click", event => {
    const btn = event.target;
    if (!btn) return;

    const player = moves[btn.dataset.move];
    play(player);
});

resetButton.addEventListener("click", reset);

reset();