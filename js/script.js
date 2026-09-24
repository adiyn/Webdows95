const startMenuBtn = document.getElementById("start-menu-btn");
const startMenu = document.getElementById("start-menu");
const GRID_SIZE = 80;

let highestZ = 10;
let draggedItem = null;
let offsetX = 0;
let offsetY = 0;
let isDragging = false;


// Window Behaviours
function bringToFront(windowElement) {
    highestZ++;
    windowElement.style.zIndex = highestZ;
}

function openWindow(windowId) {
    const win = document.getElementById(windowId);
    if (!win) return;
    win.removeAttribute("hidden");
    bringToFront(win);
    closeStartMenu();
}

function closeWindow(windowId) {
    const win = document.getElementById(windowId);
    if (win) {
        win.setAttribute("hidden", "");
    }
}

function toggleStartMenu() {
    const isOpen = !startMenu.hasAttribute("hidden");
    if (isOpen) {
        closeStartMenu();
    } else {
        startMenu.removeAttribute("hidden");
        startMenuBtn.setAttribute("aria-expanded", "true");
    }
}

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

// Drag and Drop Grid System
document.addEventListener('mousedown', (e) => {
    const icon = e.target.closest('.desktop-icon');
    if (icon) {
        draggedItem = icon;
        isDragging = false;
        const rect = icon.getBoundingClientRect();
        
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        
        bringToFront(icon);
    }
});

document.addEventListener('mousemove', (e) => {
    if (draggedItem) {
        isDragging = true;
        draggedItem.style.left = (e.clientX - offsetX) + 'px';
        draggedItem.style.top = (e.clientY - offsetY) + 'px';
    }
});

document.addEventListener('mouseup', (e) => {
    if (draggedItem) {
        if (isDragging) {
            const currentX = parseFloat(draggedItem.style.left) || 0;
            const currentY = parseFloat(draggedItem.style.top) || 0;
            
            const snappedX = Math.round(currentX / GRID_SIZE) * GRID_SIZE;
            const snappedY = Math.round(currentY / GRID_SIZE) * GRID_SIZE;
            
            draggedItem.style.left = snappedX + 'px';
            draggedItem.style.top = snappedY + 'px';
            
            setTimeout(() => { isDragging = false; }, 0);
        }
        draggedItem = null;
    }
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