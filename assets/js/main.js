// Main JavaScript file for Railway Shift Management System

document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))

  // Auto-hide alerts after 5 seconds
  setTimeout(() => {
    var alerts = document.querySelectorAll(".alert-dismissible")
    alerts.forEach((alert) => {
      var bsAlert = new bootstrap.Alert(alert)
      bsAlert.close()
    })
  }, 5000)

  // Sidebar active link highlighting
  highlightActiveNavLink()

  // Form validation
  setupFormValidation()

  // Real-time clock
  updateClock()
  setInterval(updateClock, 1000)
})

// Highlight active navigation link
function highlightActiveNavLink() {
  const currentPath = window.location.pathname.split("/").pop()
  const navLinks = document.querySelectorAll(".sidebar .nav-link")

  navLinks.forEach((link) => {
    link.classList.remove("active")
    const href = link.getAttribute("href")
    if (href === currentPath || (currentPath === "" && href === "index.php")) {
      link.classList.add("active")
    }
  })
}

// Form validation setup
function setupFormValidation() {
  const forms = document.querySelectorAll(".needs-validation")

  Array.prototype.slice.call(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add("was-validated")
      },
      false,
    )
  })
}

// Update real-time clock
function updateClock() {
  const clockElement = document.getElementById("real-time-clock")
  if (clockElement) {
    const now = new Date()
    const timeString = now.toLocaleTimeString("en-US", {
      hour12: false,
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    })
    clockElement.textContent = timeString
  }
}

// Shift assignment functions
function assignShift(shiftId, staffId, date) {
  if (confirm("Are you sure you want to assign this shift?")) {
    const formData = new FormData()
    formData.append("action", "assign")
    formData.append("shift_id", shiftId)
    formData.append("staff_id", staffId)
    formData.append("assignment_date", date)

    fetch("ajax/shift_assignment.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert("Shift assigned successfully!", "success")
          location.reload()
        } else {
          showAlert("Failed to assign shift: " + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showAlert("An error occurred while assigning the shift.", "danger")
      })
  }
}

// Update shift status
function updateShiftStatus(assignmentId, status) {
  const formData = new FormData()
  formData.append("action", "update_status")
  formData.append("assignment_id", assignmentId)
  formData.append("status", status)

  fetch("ajax/shift_assignment.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Status updated successfully!", "success")
        location
        showAlert("Status updated successfully!", "success")
        location.reload()
      } else {
        showAlert("Failed to update status: " + data.message, "danger")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("An error occurred while updating status.", "danger")
    })
}

// Show alert messages
function showAlert(message, type) {
  const alertContainer = document.getElementById("alert-container") || document.body
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  alertContainer.insertBefore(alertDiv, alertContainer.firstChild)

  // Auto-hide after 5 seconds
  setTimeout(() => {
    const bsAlert = new bootstrap.Alert(alertDiv)
    bsAlert.close()
  }, 5000)
}

// Shift swap request functions
function requestShiftSwap(originalShiftId, targetShiftId, swapDate) {
  const reason = prompt("Please provide a reason for the shift swap:")
  if (reason === null) return // User cancelled

  const formData = new FormData()
  formData.append("action", "request_swap")
  formData.append("original_shift_id", originalShiftId)
  formData.append("target_shift_id", targetShiftId)
  formData.append("swap_date", swapDate)
  formData.append("reason", reason)

  fetch("ajax/shift_swap.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Shift swap request submitted successfully!", "success")
        location.reload()
      } else {
        showAlert("Failed to submit swap request: " + data.message, "danger")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("An error occurred while submitting the swap request.", "danger")
    })
}

// Approve/Reject shift swap
function processSwapRequest(requestId, action) {
  const actionText = action === "approve" ? "approve" : "reject"
  if (confirm(`Are you sure you want to ${actionText} this swap request?`)) {
    const formData = new FormData()
    formData.append("action", "process_swap")
    formData.append("request_id", requestId)
    formData.append("decision", action)

    fetch("ajax/shift_swap.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert(`Swap request ${actionText}d successfully!`, "success")
          location.reload()
        } else {
          showAlert(`Failed to ${actionText} swap request: ` + data.message, "danger")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showAlert(`An error occurred while processing the swap request.`, "danger")
      })
  }
}

// Calendar functions
function initializeCalendar() {
  const calendarEl = document.getElementById("calendar")
  if (calendarEl) {
    // Initialize calendar with shift data
    loadCalendarEvents()
  }
}

function loadCalendarEvents() {
  fetch("ajax/get_calendar_events.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderCalendarEvents(data.events)
      }
    })
    .catch((error) => {
      console.error("Error loading calendar events:", error)
    })
}

// Export functions
function exportToExcel(reportType, dateRange) {
  const params = new URLSearchParams({
    type: reportType,
    start_date: dateRange.start,
    end_date: dateRange.end,
    format: "excel",
  })

  window.open(`reports/export.php?${params.toString()}`, "_blank")
}

function exportToPDF(reportType, dateRange) {
  const params = new URLSearchParams({
    type: reportType,
    start_date: dateRange.start,
    end_date: dateRange.end,
    format: "pdf",
  })

  window.open(`reports/export.php?${params.toString()}`, "_blank")
}

// Search and filter functions
function filterTable(tableId, searchTerm) {
  const table = document.getElementById(tableId)
  const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr")

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i]
    const cells = row.getElementsByTagName("td")
    let found = false

    for (let j = 0; j < cells.length; j++) {
      const cellText = cells[j].textContent || cells[j].innerText
      if (cellText.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1) {
        found = true
        break
      }
    }

    row.style.display = found ? "" : "none"
  }
}

// Notification functions
function markNotificationAsRead(notificationId) {
  const formData = new FormData()
  formData.append("action", "mark_read")
  formData.append("notification_id", notificationId)

  fetch("ajax/notifications.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const notificationElement = document.getElementById(`notification-${notificationId}`)
        if (notificationElement) {
          notificationElement.classList.remove("unread")
        }
        updateNotificationCount()
      }
    })
    .catch((error) => {
      console.error("Error marking notification as read:", error)
    })
}

function updateNotificationCount() {
  fetch("ajax/get_notification_count.php")
    .then((response) => response.json())
    .then((data) => {
      const badge = document.querySelector(".notification-badge")
      if (badge) {
        if (data.count > 0) {
          badge.textContent = data.count
          badge.style.display = "inline"
        } else {
          badge.style.display = "none"
        }
      }
    })
    .catch((error) => {
      console.error("Error updating notification count:", error)
    })
}

// Utility functions
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  })
}

function formatTime(timeString) {
  const time = new Date(`2000-01-01 ${timeString}`)
  return time.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  })
}

// Initialize page-specific functions
function initializePage() {
  const currentPage = window.location.pathname.split("/").pop()

  switch (currentPage) {
    case "index.php":
    case "":
      initializeDashboard()
      break
    case "schedule.php":
      initializeCalendar()
      break
    case "reports.php":
      initializeReports()
      break
  }
}

function initializeDashboard() {
  // Load dashboard-specific data
  updateNotificationCount()
  loadTodayStats()
}

function loadTodayStats() {
  fetch("ajax/get_dashboard_stats.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateStatsCards(data.stats)
      }
    })
    .catch((error) => {
      console.error("Error loading dashboard stats:", error)
    })
}

function updateStatsCards(stats) {
  const elements = {
    "today-shifts": stats.todayShifts,
    "active-staff": stats.activeStaff,
    "pending-requests": stats.pendingRequests,
    alerts: stats.alerts,
  }

  Object.keys(elements).forEach((id) => {
    const element = document.getElementById(id)
    if (element) {
      element.textContent = elements[id]
    }
  })
}

function renderCalendarEvents(events) {
  const calendarEl = document.getElementById("calendar")
  if (calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ["interaction", "dayGrid", "timeGrid", "list"],
      header: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
      },
      defaultView: "dayGridMonth",
      events: events,
      eventClick: (info) => {
        // Handle event click (e.g., show details)
        alert("Event: " + info.event.title)
        info.jsEvent.preventDefault() // prevent browser from navigating to the link
      },
    })
    calendar.render()
  }
}

function initializeReports() {
  // Date range picker initialization
  $(() => {
    $('input[name="daterange"]').daterangepicker(
      {
        opens: "left",
      },
      (start, end, label) => {
        console.log("A new date selection was made: " + start.format("YYYY-MM-DD") + " to " + end.format("YYYY-MM-DD"))
      },
    )
  })

  // Export button event listeners
  document.getElementById("export-excel-btn").addEventListener("click", () => {
    const dateRangeInput = document.querySelector('input[name="daterange"]')
    const dateRange = {
      start: dateRangeInput.value.split(" - ")[0],
      end: dateRangeInput.value.split(" - ")[1],
    }
    exportToExcel("shift_report", dateRange)
  })

  document.getElementById("export-pdf-btn").addEventListener("click", () => {
    const dateRangeInput = document.querySelector('input[name="daterange"]')
    const dateRange = {
      start: dateRangeInput.value.split(" - ")[0],
      end: dateRangeInput.value.split(" - ")[1],
    }
    exportToPDF("shift_report", dateRange)
  })
}

// Initialize everything when page loads
initializePage()
