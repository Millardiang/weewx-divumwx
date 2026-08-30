document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("cardContainer");
  const resetBtn = document.getElementById("resetLayout");

  if (!container || !resetBtn) {
    console.warn("Card container or reset button not found.");
    return;
  }

  // --- Drag and drop cards ---
  container.querySelectorAll("[class^='card']").forEach(card => {
    card.setAttribute("draggable", "true");
  });

  const defaultOrder = [...container.children].map(el =>
    [...el.classList].find(c => c.startsWith("card"))
  );

  const savedOrder = JSON.parse(localStorage.getItem("cardOrder"));
  if (savedOrder) {
    savedOrder.forEach(cls => {
      const el = container.querySelector("." + cls);
      if (el) container.appendChild(el);
    });
  }

  let dragged;
  container.addEventListener("dragstart", e => {
    if (!e.target.matches("[class^='card']")) return;
    dragged = e.target;
    e.target.classList.add("dragging");
  });

  container.addEventListener("dragend", e => {
    e.target.classList.remove("dragging");
    saveOrder();
  });

  container.addEventListener("dragover", e => {
    e.preventDefault();
    const afterEl = getDragAfterElement(container, e.clientY);
    if (afterEl == null) {
      container.appendChild(dragged);
    } else {
      container.insertBefore(dragged, afterEl);
    }
  });

  function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll("[class^='card']:not(.dragging)")];
    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
  }

  function saveOrder() {
    const order = [...container.children].map(el =>
      [...el.classList].find(c => c.startsWith("card"))
    );
    localStorage.setItem("cardOrder", JSON.stringify(order));
    toggleResetButton(order);
  }

  function toggleResetButton(currentOrder) {
    if (JSON.stringify(currentOrder) === JSON.stringify(defaultOrder)) {
      resetBtn.classList.add("disabled");
      resetBtn.setAttribute("data-tooltip", "Layout already default");
    } else {
      resetBtn.classList.remove("disabled");
      resetBtn.setAttribute("data-tooltip", "Reset layout to default");
    }
  }

  resetBtn.addEventListener("click", e => {
    e.preventDefault();
    if (resetBtn.classList.contains("disabled")) return;
    if (confirm("Are you sure you want to reset the layout to default?")) {
      localStorage.removeItem("cardOrder");
      defaultOrder.forEach(cls => {
        const el = container.querySelector("." + cls);
        if (el) container.appendChild(el);
      });
      toggleResetButton(defaultOrder);
    }
  });

  const initialOrder = savedOrder || defaultOrder;
  toggleResetButton(initialOrder);

  // --- Accordion menu ---
  document.querySelectorAll(".submenu-toggle").forEach(toggle => {
    const submenu = toggle.nextElementSibling;
    const key = "submenuState";
    const savedState = localStorage.getItem(key);

    if (savedState && savedState === toggle.textContent.replace(/▸|▼/g, "").trim()) {
      submenu.classList.add("open");
      toggle.textContent = toggle.textContent.replace("▸", "▼");
    }

    toggle.addEventListener("click", () => {
      const title = toggle.textContent.replace(/▸|▼/g, "").trim();
      const isOpen = submenu.classList.contains("open");

      document.querySelectorAll(".submenu").forEach(sm => sm.classList.remove("open"));
      document.querySelectorAll(".submenu-toggle").forEach(tg => {
        tg.textContent = tg.textContent.replace("▼", "▸");
      });

      if (isOpen) {
        localStorage.removeItem(key);
      } else {
        submenu.classList.add("open");
        toggle.textContent = toggle.textContent.replace("▸", "▼");
        localStorage.setItem(key, title);
      }
    });
  });
});
