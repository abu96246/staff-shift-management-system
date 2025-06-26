document.addEventListener("DOMContentLoaded", () => {
  console.log("Profile page loaded")

  // Initialize profile page functionality
  initializeProfilePage()
})

function initializeProfilePage() {
  // Save profile button
  const saveProfileBtn = document.getElementById("saveProfile")
  if (saveProfileBtn) {
    saveProfileBtn.addEventListener("click", () => {
      saveProfileChanges()
    })
  }

  // Change photo button
  const changePhotoBtn = document.getElementById("changePhoto")
  if (changePhotoBtn) {
    changePhotoBtn.addEventListener("click", () => {
      // Simulate file input click
      const fileInput = document.createElement("input")
      fileInput.type = "file"
      fileInput.accept = "image/*"
      fileInput.style.display = "none"

      fileInput.addEventListener("change", (e) => {
        if (e.target.files && e.target.files[0]) {
          const reader = new FileReader()

          reader.onload = (e) => {
            // Update profile image
            const profileImage = document.querySelector(".card-body img.rounded-circle")
            if (profileImage) {
              profileImage.src = e.target.result
            }
          }

          reader.readAsDataURL(e.target.files[0])
        }
      })

      document.body.appendChild(fileInput)
      fileInput.click()
      document.body.removeChild(fileInput)
    })
  }

  // Change password button
  const changePasswordBtn = document.getElementById("changePassword")
  if (changePasswordBtn) {
    changePasswordBtn.addEventListener("click", () => {
      changePassword()
    })
  }
}

function saveProfileChanges() {
  // Show loading indicator
  showLoading("Saving profile changes...")

  // Get form values
  const firstName = document.getElementById("firstName").value
  const lastName = document.getElementById("lastName").value
  const email = document.getElementById("email").value
  const phone = document.getElementById("phone").value
  const department = document.getElementById("department").value
  const position = document.getElementById("position").value
  const bio = document.getElementById("bio").value

  // Validate form
  if (!firstName || !lastName || !email) {
    hideLoading()
    alert("Please fill in all required fields")
    return
  }

  // Simulate API call
  setTimeout(() => {
    // Hide loading indicator
    hideLoading()

    // Show success message
    alert("Profile changes saved successfully")

    // Update user name in dropdown
    const userDropdown = document.getElementById("userDropdown")
    if (userDropdown) {
      userDropdown.innerHTML = `<i class="bi bi-person-circle"></i> ${firstName} ${lastName}`
    }
  }, 1000)
}

function changePassword() {
  // Get password values
  const currentPassword = document.getElementById("currentPassword").value
  const newPassword = document.getElementById("newPassword").value
  const confirmPassword = document.getElementById("confirmPassword").value

  // Validate passwords
  if (!currentPassword) {
    alert("Please enter your current password")
    return
  }

  if (!newPassword) {
    alert("Please enter a new password")
    return
  }

  if (newPassword !== confirmPassword) {
    alert("New passwords do not match")
    return
  }

  // Show loading indicator
  showLoading("Changing password...")

  // Simulate API call
  setTimeout(() => {
    // Hide loading indicator
    hideLoading()

    // Show success message
    alert("Password changed successfully")

    // Clear password fields
    document.getElementById("currentPassword").value = ""
    document.getElementById("newPassword").value = ""
    document.getElementById("confirmPassword").value = ""
  }, 1000)
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
