document.addEventListener("DOMContentLoaded", () => {
  const root = document.documentElement;
  const savedTheme = localStorage.getItem("theme") || "light";
  root.setAttribute("data-bs-theme", savedTheme);

  document.querySelectorAll("#themeToggle").forEach((button) => {
    button.textContent = savedTheme === "dark" ? "Light mode" : "Dark mode";
    button.addEventListener("click", () => {
      const next = root.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
      root.setAttribute("data-bs-theme", next);
      localStorage.setItem("theme", next);
      button.textContent = next === "dark" ? "Light mode" : "Dark mode";
    });
  });

  document.querySelectorAll(".toast").forEach((toast) => {
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 4200 }).show();
  });

  document.querySelectorAll(".needs-validation").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  const imageInput = document.getElementById("imageInput");
  const previewWrap = document.getElementById("imagePreviewWrap");
  const previewImage = document.getElementById("imagePreview");

  if (imageInput && previewWrap && previewImage) {
    imageInput.addEventListener("change", () => {
      const file = imageInput.files && imageInput.files[0];
      if (!file) {
        previewWrap.classList.add("d-none");
        previewImage.removeAttribute("src");
        return;
      }

      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024) {
        imageInput.setCustomValidity("Use a JPG, PNG, or WEBP image up to 5 MB.");
        imageInput.reportValidity();
        previewWrap.classList.add("d-none");
        return;
      }

      imageInput.setCustomValidity("");
      previewImage.src = URL.createObjectURL(file);
      previewWrap.classList.remove("d-none");
    });
  }

  if (window.dashboardStats && window.Chart) {
    const itemCanvas = document.getElementById("itemsChart");
    const categoryCanvas = document.getElementById("categoryChart");
    const textColor = getComputedStyle(document.body).color;

    if (itemCanvas) {
      new Chart(itemCanvas, {
        type: "bar",
        data: {
          labels: ["Lost", "Found", "Claimed", "Pending Claims"],
          datasets: [{
            label: "Records",
            data: window.dashboardStats.itemTotals,
            backgroundColor: ["#f59e0b", "#06b6d4", "#22c55e", "#6366f1"],
            borderRadius: 8
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { color: textColor }, grid: { display: false } },
            y: { ticks: { color: textColor, precision: 0 } }
          }
        }
      });
    }

    if (categoryCanvas) {
      new Chart(categoryCanvas, {
        type: "doughnut",
        data: {
          labels: window.dashboardStats.categoryLabels,
          datasets: [{
            data: window.dashboardStats.categoryTotals,
            backgroundColor: ["#2563eb", "#0f766e", "#f59e0b", "#e11d48", "#7c3aed", "#0891b2", "#64748b"]
          }]
        },
        options: { plugins: { legend: { position: "bottom" } } }
      });
    }
  }
});
