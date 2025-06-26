document.addEventListener("DOMContentLoaded", () => {
  console.log("Settings page loaded")

  // Initialize settings page functionality
  initializeSettingsPage()
})

function initializeSettingsPage() {
  // Save settings button
  const saveSettingsBtn = document.getElementById("saveSettings")
  if (saveSettingsBtn) {
    saveSettingsBtn.addEventListener("click", () => {
      saveSettingsChanges()
    })
  }

  // Theme selection
  const themeRadios = document.querySelectorAll('input[name="theme"]')
  themeRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      updateTheme(this.id)
    })
  })

  // Primary color picker
  const primaryColorPicker = document.getElementById("primaryColor")
  if (primaryColorPicker) {
    primaryColorPicker.addEventListener("change", function () {
      updatePrimaryColor(this.value)
    })
  }

  // Sidebar position
  const sidebarPositionSelect = document.getElementById("sidebarPosition")
  if (sidebarPositionSelect) {
    sidebarPositionSelect.addEventListener("change", function () {
      updateSidebarPosition(this.value)
    })
  }

  // Compact mode
  const compactModeSwitch = document.getElementById("compactMode")
  if (compactModeSwitch) {
    compactModeSwitch.addEventListener("change", function () {
      updateCompactMode(this.checked)
    })
  }

  // Clear cache button
  const clearCacheBtn = document.querySelector('button:contains("Clear Cache")')
  if (clearCacheBtn) {
    clearCacheBtn.addEventListener("click", () => {
      clearCache()
    })
  }

  // Reset to defaults button
  const resetDefaultsBtn = document.querySelector('button:contains("Reset to Defaults")')
  if (resetDefaultsBtn) {
    resetDefaultsBtn.addEventListener("click", () => {
      resetToDefaults()
    })
  }

  // Backup now button
  const backupNowBtn = document.querySelector('button:contains("Backup Now")')
  if (backupNowBtn) {
    backupNowBtn.addEventListener("click", () => {
      backupNow()
    })
  }

  // Restore from backup button
  const restoreBackupBtn = document.querySelector('button:contains("Restore from Backup")')
  if (restoreBackupBtn) {
    restoreBackupBtn.addEventListener("click", () => {
      restoreFromBackup()
    })
  }
}

function saveSettingsChanges() {
  // Show loading indicator
  showLoading("Saving settings...")

  // Get active tab
  const activeTab = document.querySelector(".tab-pane.active")
  const tabId = activeTab.id

  // Get settings values based on active tab
  let settings = {}

  switch (tabId) {
    case "general":
      settings = {
        systemName: document.getElementById("systemName").value,
        timezone: document.getElementById("timezone").value,
        dateFormat: document.getElementById("dateFormat").value,
        timeFormat: document.getElementById("timeFormat").value,
        language: document.getElementById("language").value,
      }
      break

    case "appearance":
      settings = {
        theme: document.querySelector('input[name="theme"]:checked').id,
        primaryColor: document.getElementById("primaryColor").value,
        fontSize: document.getElementById("fontSize").value,
        sidebarPosition: document.getElementById("sidebarPosition").value,
        compactMode: document.getElementById("compactMode").checked,
      }
      break

    case "notifications":
      settings = {
        enableNotifications: document.getElementById("enableNotifications").checked,
        conflictNotifications: document.getElementById("conflictNotifications").checked,
        delayNotifications: document.getElementById("delayNotifications").checked,
        scheduleNotifications: document.getElementById("scheduleNotifications").checked,
        systemNotifications: document.getElementById("systemNotifications").checked,
        notificationSound: document.getElementById("notificationSound").value,
        desktopNotifications: document.getElementById("desktopNotifications").checked,
        emailNotifications: document.getElementById("emailNotifications").checked,
      }
      break

    case "security":
      settings = {
        sessionTimeout: document.getElementById("sessionTimeout").value,
        twoFactorAuth: document.getElementById("twoFactorAuth").checked,
        passwordPolicy: document.getElementById("passwordPolicy").value,
        passwordExpiry: document.getElementById("passwordExpiry").value,
        loginHistory: document.getElementById("loginHistory").checked,
      }
      break

    case "system":
      settings = {
        dataRefreshRate: document.getElementById("dataRefreshRate").value,
        logLevel: document.getElementById("logLevel").value,
        enableCache: document.getElementById("enableCache").checked,
        enableAnalytics: document.getElementById("enableAnalytics").checked,
        maxResults: document.getElementById("maxResults").value,
      }
      break

    case "backup":
      settings = {
        backupSchedule: document.getElementById("backupSchedule").value,
        backupTime: document.getElementById("backupTime").value,
        backupRetention: document.getElementById("backupRetention").value,
        compressBackup: document.getElementById("compressBackup").checked,
        backupLocation: document.getElementById("backupLocation").value,
      }
      break
  }

  console.log("Saving settings for tab:", tabId, settings)

  // Simulate API call
  setTimeout(() => {
    // Hide loading indicator
    hideLoading()

    // Show success message
    alert("Settings saved successfully")
  }, 1000)
}

function updateTheme(themeId) {
  console.log("Updating theme to:", themeId)

  // Apply theme changes
  const body = document.body

  switch (themeId) {
    case "lightTheme":
      body.classList.remove("dark-theme")
      body.classList.add("light-theme")
      break

    case "darkTheme":
      body.classList.remove("light-theme")
      body.classList.add("dark-theme")
      break

    case "systemTheme":
      body.classList.remove("light-theme", "dark-theme")
      // Use system preference
      if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        body.classList.add("dark-theme")
      } else {
        body.classList.add("light-theme")
      }
      break
  }
}

function updatePrimaryColor(color) {
  console.log("Updating primary color to:", color)

  // Create a style element if it doesn't exist
  let styleElement = document.getElementById("customStyles")
  if (!styleElement) {
    styleElement = document.createElement("style")
    styleElement.id = "customStyles"
    document.head.appendChild(styleElement)
  }

  // Update CSS variables
  styleElement.textContent = `
    :root {
      --primary-color: ${color};
    }
  `
}

function updateSidebarPosition(position) {
  console.log("Updating sidebar position to:", position)

  const wrapper = document.querySelector(".wrapper")
  const sidebar = document.getElementById("sidebar")
  const content = document.getElementById("content")

  if (wrapper && sidebar && content) {
    if (position === "right") {
      wrapper.classList.add("sidebar-right")
      sidebar.style.right = "0"
      sidebar.style.left = "auto"
      content.style.right = "auto"
      content.style.left = "0"
    } else {
      wrapper.classList.remove("sidebar-right")
      sidebar.style.left = "0"
      sidebar.style.right = "auto"
      content.style.left = "auto"
      content.style.right = "0"
    }
  }
}

function updateCompactMode(enabled) {
  console.log("Updating compact mode to:", enabled)

  const body = document.body

  if (enabled) {
    body.classList.add("compact-mode")
  } else {
    body.classList.remove("compact-mode")
  }
}

function clearCache() {
  // Show loading indicator
  showLoading("Clearing cache...")

  // Simulate API call
  setTimeout(() => {
    // Hide loading indicator
    hideLoading()

    // Show success message
    alert("Cache cleared successfully")
  }, 1500)
}

function resetToDefaults() {
  if (confirm("Are you sure you want to reset all settings to default values? This cannot be undone.")) {
    // Show loading indicator
    showLoading("Resetting to defaults...")

    // Simulate API call
    setTimeout(() => {
      // Hide loading indicator
      hideLoading()

      // Show success message
      alert("Settings reset to defaults successfully")

      // Reload page
      window.location.reload()
    }, 2000)
  }
}

function backupNow() {
  // Show loading indicator
  showLoading("Creating backup...")

  // Simulate API call
  setTimeout(() => {
    // Hide loading indicator
    hideLoading()

    // Show success message
    alert("Backup created successfully")

    // Add new backup to list
    const backupsList = document.querySelector(".list-group")
    if (backupsList) {
      const now = new Date()
      const formattedDate = now.toISOString().replace("T", " ").substring(0, 19)

      const newBackup = document.createElement("a")
      newBackup.href = "#"
      newBackup.className = "list-group-item list-group-item-action"
      newBackup.innerHTML = `
        <div class="d-flex w-100 justify-content-between">
          <h6 class="mb-1">Full Backup</h6>
          <small>${formattedDate}</small>
        </div>
        <p class="mb-1">Size: 45.5 MB</p>
      `

      backupsList.insertBefore(newBackup, backupsList.firstChild)
    }
  }, 3000)
}

function restoreFromBackup() {
  if (confirm("Are you sure you want to restore from backup? Current data will be overwritten.")) {
    // Show loading indicator
    showLoading("Restoring from backup...")

    // Simulate API call
    setTimeout(() => {
      // Hide loading indicator
      hideLoading()

      // Show success message
      alert("Restore completed successfully")

      // Reload page after short delay
      setTimeout(() => {
        window.location.reload()
      }, 1000)
    }, 4000)
  }
}

// Show loading indicator
function showLoading(message) {
  // Create loading overlay if it doesn't exist
  let loadingOverlay = document.getElementById("loadingOverlay")

  if (!loadingOverlay) {
    loadingOverlay = document.createElement("div")
    loadingOverlay.id = "loadingOverlay"
    loadingOverlay.className =
      "position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50"
    loadingOverlay.style.zIndex = "9999"

    loadingOverlay.innerHTML = `
      <div class="card p-4">
        <div class="d-flex align-items-center">
          <div class="spinner-border text-primary me-3" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div id="loadingMessage">Loading...</div>
        </div>
      </div>
    `

    document.body.appendChild(loadingOverlay)
  }

  // Update loading message
  const loadingMessage = document.getElementById("loadingMessage")
  if (loadingMessage) {
    loadingMessage.textContent = message || "Loading..."
  }

  // Show loading overlay
  loadingOverlay.classList.remove("d-none")
}

// Hide loading indicator
function hideLoading() {
  // Hide loading overlay
  const loadingOverlay = document.getElementById("loadingOverlay")
  if (loadingOverlay) {
    loadingOverlay.classList.add("d-none")
  }
}

// Helper function to find buttons by text content
Element.prototype.contains = function (text) {
  return this.textContent.includes(text)
}
