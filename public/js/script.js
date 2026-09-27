const startMenuBtn = document.getElementById("start-menu-btn");
const startMenu = document.getElementById("start-menu");
const GRID_SIZE = 80;

let highestZ = 10;
let draggedItem = null;
let startX = 0;
let startY = 0;
let offsetX = 0;
let offsetY = 0;
let isDragging = false;
let draggedWindow = null;
let winOffsetX = 0;
let winOffsetY = 0;
let oldX = 0;
let oldY = 0;

/**
 * 
 * @param {*} windowElement 
 */
function bringToFront(windowElement) {
    highestZ++;
    windowElement.style.zIndex = highestZ;
}

/**
 * 
 * @param {*} windowId 
 * @returns 
 */
function openWindow(windowId) {
    const win = document.getElementById(windowId);
    if (!win) return;
    win.removeAttribute("hidden");
    bringToFront(win);
    closeStartMenu();
}

/**
 * 
 * @param {*} windowId 
 */
function closeWindow(windowId) {
    const win = document.getElementById(windowId);
    if (win) {
        win.setAttribute("hidden", "");
    }
}

/**
 * 
 */
function toggleStartMenu() {
    const isOpen = !startMenu.hasAttribute("hidden");
    if (isOpen) {
        closeStartMenu();
    } else {
        startMenu.removeAttribute("hidden");
        startMenuBtn.setAttribute("aria-expanded", "true");
    }
}

/**
 * 
 */
function closeStartMenu() {
    startMenu.setAttribute("hidden", "");
    startMenuBtn.setAttribute("aria-expanded", "false");
}

startMenuBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    toggleStartMenu();
});

document.addEventListener("click", (e) => {
    if (!startMenu.contains(e.target) && e.target !== startMenuBtn) {
        closeStartMenu();
    }
});

document.querySelectorAll("[data-close]").forEach((btn) => {
    btn.addEventListener("click", () => {
        const targetId = btn.getAttribute("data-close");
        closeWindow(targetId);
    });
});

document.querySelectorAll(".window").forEach((win) => {
    win.addEventListener("mousedown", () => bringToFront(win));
});

document.addEventListener('mousedown', (e) => {
    const icon = e.target.closest('.desktop-icon');
    if (icon) {
        draggedItem = icon;
        isDragging = false;
        startX = e.clientX;
        startY = e.clientY;

        oldX = parseFloat(icon.style.left) || 0;
        oldY = parseFloat(icon.style.top) || 0;

        const rect = icon.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        bringToFront(icon);
    }
});

document.addEventListener('mousemove', (e) => {
    if (draggedItem) {
        const dx = Math.abs(e.clientX - startX);
        const dy = Math.abs(e.clientY - startY);

        if (dx > 5 || dy > 5) {
            isDragging = true;

            const desktop = document.getElementById('desktop');
            const desktopRect = desktop.getBoundingClientRect();

            let currentLeft = e.clientX - desktopRect.left - offsetX;
            let currentTop = e.clientY - desktopRect.top - offsetY;

            const maxX = desktop.clientWidth - draggedItem.offsetWidth;
            const maxY = desktop.clientHeight - draggedItem.offsetHeight;

            currentLeft = Math.max(0, Math.min(currentLeft, maxX));
            currentTop = Math.max(0, Math.min(currentTop, maxY));

            draggedItem.style.left = `${currentLeft}px`;
            draggedItem.style.top = `${currentTop}px`;
        }
    }
});

document.addEventListener('mouseup', () => {
    if (draggedItem && isDragging) {
        const desktop = document.getElementById('desktop');
        const maxCols = Math.floor(desktop.clientWidth / GRID_SIZE);
        const maxRows = Math.floor(desktop.clientHeight / GRID_SIZE);

        const currentX = parseFloat(draggedItem.style.left) || 0;
        const currentY = parseFloat(draggedItem.style.top) || 0;

        let col = Math.round(currentX / GRID_SIZE);
        let row = Math.round(currentY / GRID_SIZE);

        col = Math.max(0, Math.min(col, maxCols - 1));
        row = Math.max(0, Math.min(row, maxRows - 1));

        const nx = col * GRID_SIZE;
        const ny = row * GRID_SIZE;

        const isOccupied = Array.from(document.querySelectorAll('.desktop-icon')).some((icon) => {
            if (icon === draggedItem) return false;
            const otherX = parseFloat(icon.style.left) || 0;
            const otherY = parseFloat(icon.style.top) || 0;
            return otherX === nx && otherY === ny;
        });

        if (isOccupied) {
            draggedItem.style.left = `${oldX}px`;
            draggedItem.style.top = `${oldY}px`;
        } else {
            draggedItem.style.left = `${nx}px`;
            draggedItem.style.top = `${ny}px`;
        }

        setTimeout(() => { isDragging = false; }, 50);
    }
    draggedItem = null;
});

document.querySelectorAll("[data-target]").forEach((trigger) => {
    trigger.addEventListener("click", (e) => {
        if (isDragging) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        const targetId = trigger.getAttribute("data-target");
        openWindow(targetId);
    });
});

document.addEventListener('mousedown', (e) => {
    const titleBar = e.target.closest('.title-bar');
    if (!titleBar) return;

    if (e.target.closest('.title-bar-button')) return;

    const win = titleBar.closest('.window');
    if (win) {
        draggedWindow = win;
        bringToFront(win);

        const rect = win.getBoundingClientRect();
        winOffsetX = e.clientX - rect.left;
        winOffsetY = e.clientY - rect.top;
    }
});

document.addEventListener('mousemove', (e) => {
    if (draggedWindow) {
        const desktop = document.getElementById('desktop');
        const desktopRect = desktop.getBoundingClientRect();

        let newLeft = e.clientX - desktopRect.left - winOffsetX;
        let newTop = e.clientY - desktopRect.top - winOffsetY;

        newTop = Math.max(0, newTop);

        draggedWindow.style.left = `${newLeft}px`;
        draggedWindow.style.top = `${newTop}px`;
    }
});

document.addEventListener('mouseup', () => {
    draggedWindow = null;
});