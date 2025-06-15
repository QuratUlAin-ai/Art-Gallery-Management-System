// Global variables
let currentEditingRecord = null
let currentEntity = null

// Tab switching functionality
function showTab(tabName) {
  console.log("Switching to tab:", tabName)

  // Hide all tab contents
  const tabContents = document.querySelectorAll(".tab-content")
  tabContents.forEach((tab) => {
    tab.classList.remove("active")
  })

  // Remove active class from all nav items
  const navItems = document.querySelectorAll(".nav-item")
  navItems.forEach((item) => {
    item.classList.remove("active")
  })

  // Show selected tab
  const selectedTab = document.getElementById(tabName)
  if (selectedTab) {
    selectedTab.classList.add("active")
  }

  // Add active class to clicked nav item
  if (event && event.target) {
    event.target.classList.add("active")
  }

  // Update page title and description
  const titles = {
    dashboard: { title: "Dashboard", desc: "Overview of gallery operations and analytics" },
    customer: { title: "Customer Management", desc: "Manage customer information and profiles" },
    artist: { title: "Artist Management", desc: "Manage artist profiles and information" },
    room: { title: "Room Management", desc: "Configure gallery rooms and specifications" },
    staff: { title: "Staff Management", desc: "Manage gallery staff and their roles" },
    exhibition: { title: "Exhibition Management", desc: "Create and manage gallery exhibitions" },
    artwork: { title: "Artwork Management", desc: "Catalog and manage artwork collection" },
    ticket: { title: "Ticket Management", desc: "Process and manage exhibition tickets" },
    purchase: { title: "Purchase Management", desc: "Record and manage artwork purchases" },
  }

  const pageTitle = document.getElementById("page-title")
  const pageDescription = document.getElementById("page-description")

  if (pageTitle && titles[tabName]) {
    pageTitle.textContent = titles[tabName].title
  }
  if (pageDescription && titles[tabName]) {
    pageDescription.textContent = titles[tabName].desc
  }

  currentEntity = tabName

  // Load dashboard data if dashboard tab is selected
  if (tabName === "dashboard") {
    loadDashboardData()
  }
}

// API Helper Functions
async function apiRequest(endpoint, method = "GET", data = null) {
  const config = {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
  }

  if (data && method !== "GET") {
    config.body = JSON.stringify(data)
  }

  console.log(`API Request: ${method} ${endpoint}`, data)

  try {
    const response = await fetch(`api/${endpoint}`, config)
    const responseText = await response.text()

    console.log("Raw API Response:", responseText)

    let result
    try {
      result = JSON.parse(responseText)
    } catch (parseError) {
      console.error("JSON parse error:", parseError)
      throw new Error(`Invalid response: ${responseText}`)
    }

    if (!response.ok) {
      throw new Error(result.error || `HTTP ${response.status}`)
    }

    console.log("API Success:", result)
    return result
  } catch (error) {
    console.error("API Error:", error)
    alert(`API Error: ${error.message}`)
    throw error
  }
}

// Load Dashboard Data
async function loadDashboardData() {
  try {
    console.log("Loading dashboard data...")
    const data = await apiRequest("dashboard.php")

    // Update dashboard cards
    const dashboardCards = document.querySelectorAll(".dashboard-card .query-result")

    if (dashboardCards[0]) {
      dashboardCards[0].innerHTML = `<strong>${data.total_customers || 0}</strong> registered customers`
    }

    if (dashboardCards[1]) {
      dashboardCards[1].innerHTML = `<strong>${data.active_exhibitions || 0}</strong> currently running`
    }

    if (dashboardCards[2]) {
      dashboardCards[2].innerHTML = `<strong>${data.total_artworks || 0}</strong> pieces in collection`
    }

    if (dashboardCards[3]) {
      dashboardCards[3].innerHTML = `<strong>$${Number.parseFloat(data.total_monthly_revenue || 0).toLocaleString()}</strong> this month`
    }

    // Update recent transactions
    const transactionsCard = document.querySelector(".dashboard-card:last-child .query-result")
    if (transactionsCard) {
      if (data.recent_transactions && data.recent_transactions.length > 0) {
        let transactionsHtml = '<div style="max-height: 200px; overflow-y: auto;">'
        data.recent_transactions.forEach((transaction) => {
          transactionsHtml += `
            <div style="padding: 8px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
              <div>
                <strong>${transaction.customer_name}</strong><br>
                <small>${transaction.type}: ${transaction.item_name}</small>
              </div>
              <div style="text-align: right;">
                <strong>$${Number.parseFloat(transaction.price).toLocaleString()}</strong><br>
                <small>${transaction.purchase_date}</small>
              </div>
            </div>
          `
        })
        transactionsHtml += "</div>"
        transactionsCard.innerHTML = transactionsHtml
      } else {
        transactionsCard.innerHTML = "No recent transactions found"
      }
    }
  } catch (error) {
    console.error("Failed to load dashboard data:", error)
  }
}

// Mode switching (Form vs Table view)
function switchMode(entity, mode) {
  console.log(`Switching ${entity} to ${mode} mode`)

  const formMode = document.getElementById(`${entity}-form-mode`)
  const tableMode = document.getElementById(`${entity}-table-mode`)
  const modeButtons = document.querySelectorAll(`#${entity} .mode-btn`)

  if (!formMode || !tableMode) {
    console.error(`Mode elements not found for entity: ${entity}`)
    return
  }

  // Reset mode buttons
  modeButtons.forEach((btn) => btn.classList.remove("active"))

  if (mode === "form") {
    formMode.style.display = "block"
    tableMode.style.display = "none"
    if (modeButtons[0]) modeButtons[0].classList.add("active")
    clearForm(entity)
  } else {
    formMode.style.display = "none"
    tableMode.style.display = "block"
    if (modeButtons[1]) modeButtons[1].classList.add("active")
    refreshTable(entity)
  }
}

// Clear form
function clearForm(entity) {
  console.log(`Clearing form for ${entity}`)

  const form = document.getElementById(`${entity}-form`)
  if (form) {
    form.reset()
  }

  // Reset to add mode
  currentEditingRecord = null
  updateFormMode(entity, "add")
}

// Update form mode indicator and buttons
function updateFormMode(entity, mode) {
  const indicator = document.getElementById(`${entity}-mode-indicator`)
  const deleteBtn = document.getElementById(`${entity}-delete-btn`)
  const saveBtn = document.getElementById(`${entity}-save-btn`)

  if (indicator) {
    if (mode === "add") {
      indicator.textContent = `Add New ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
      indicator.className = "form-mode-indicator"
    } else {
      indicator.textContent = `Edit ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
      indicator.className = "form-mode-indicator edit-mode"
    }
  }

  if (deleteBtn) {
    deleteBtn.style.display = mode === "add" ? "none" : "inline-block"
  }

  if (saveBtn) {
    saveBtn.textContent =
      mode === "add"
        ? `Save ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
        : `Update ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
  }
}

// Get API endpoint for entity
function getApiEndpoint(entity) {
  const entityMap = {
    customer: "customers",
    artist: "artists",
    room: "rooms",
    staff: "staff",
    exhibition: "exhibitions",
    artwork: "artworks",
    ticket: "tickets",
    purchase: "purchases",
  }
  return entityMap[entity] || entity + "s"
}

// Save record (Create or Update)
async function saveRecord(entity) {
  console.log(`Saving ${entity} record`)

  const form = document.getElementById(`${entity}-form`)
  if (!form) {
    console.error(`Form not found for entity: ${entity}`)
    alert(`Form not found for ${entity}`)
    return
  }

  const formData = new FormData(form)
  const record = {}

  // Convert FormData to object
  for (const [key, value] of formData.entries()) {
    let fieldName = key.replace(`${entity}-`, "")

    // Handle special field mappings
    if (fieldName === "start") fieldName = "start_date"
    if (fieldName === "end") fieldName = "end_date"
    if (fieldName === "room") fieldName = "room_id"
    if (fieldName === "staff") fieldName = "staff_id"
    if (fieldName === "artist") fieldName = "artist_id"
    if (fieldName === "customer") fieldName = "customer_id"
    if (fieldName === "exhibition") fieldName = "exhibition_id"
    if (fieldName === "artwork") fieldName = "artwork_id"
    if (fieldName === "purchase-date") fieldName = "purchase_date"
    if (fieldName === "date") fieldName = "purchase_date"

    record[fieldName] = value
  }

  console.log(`${entity} record data:`, record)

  // Basic validation
  if (Object.values(record).some((value) => value === "")) {
    alert("Please fill in all required fields")
    return
  }

  try {
    const endpoint = getApiEndpoint(entity)
    let response

    if (currentEditingRecord !== null) {
      // Update existing record
      record.id = currentEditingRecord
      console.log(`Updating ${entity} with ID ${currentEditingRecord}`)
      response = await apiRequest(`${endpoint}.php`, "PUT", record)
      alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} updated successfully!`)
    } else {
      // Add new record
      console.log(`Creating new ${entity}`)
      response = await apiRequest(`${endpoint}.php`, "POST", record)
      alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} added successfully!`)
    }

    console.log("Save response:", response)

    // Clear form and refresh table if in table view
    clearForm(entity)
    const tableMode = document.getElementById(`${entity}-table-mode`)
    if (tableMode && tableMode.style.display !== "none") {
      refreshTable(entity)
    }
  } catch (error) {
    console.error("Save error:", error)
  }
}

// Delete record
async function deleteRecord(entity) {
  if (currentEditingRecord !== null) {
    if (confirm(`Are you sure you want to delete this ${entity} record?`)) {
      try {
        const endpoint = getApiEndpoint(entity)
        await apiRequest(`${endpoint}.php`, "DELETE", { id: currentEditingRecord })
        alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} deleted successfully!`)
        clearForm(entity)
        refreshTable(entity)
      } catch (error) {
        console.error("Delete error:", error)
      }
    }
  }
}

// Refresh table data
async function refreshTable(entity) {
  console.log(`Refreshing table for ${entity}`)

  const table = document.getElementById(`${entity}-table`)
  if (!table) {
    console.error(`Table not found for entity: ${entity}`)
    return
  }

  const tbody = table.querySelector("tbody")
  if (!tbody) {
    console.error(`Table body not found for entity: ${entity}`)
    return
  }

  try {
    const endpoint = getApiEndpoint(entity)
    const data = await apiRequest(`${endpoint}.php`)

    // Clear existing rows
    tbody.innerHTML = ""

    if (!data || data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="100%" class="no-data">No ${entity} records found. Add some ${entity}s to see them here.</td></tr>`
      return
    }

    // Add data rows
    data.forEach((record) => {
      const row = createTableRow(entity, record)
      tbody.appendChild(row)
    })
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="100%" class="no-data">Error loading ${entity} data. Please try again.</td></tr>`
    console.error("Refresh table error:", error)
  }
}

// Create table row
function createTableRow(entity, record) {
  const row = document.createElement("tr")

  // Add data cells based on entity type
  const fields = getEntityFields(entity)
  fields.forEach((field) => {
    const cell = document.createElement("td")
    const value = record[field] || ""

    // Special formatting for certain fields
    if (field === "status" && entity === "artwork") {
      cell.innerHTML = `<span class="status-badge status-${value.toLowerCase().replace(" ", "")}">${value}</span>`
    } else if (field.includes("price") && value) {
      cell.textContent = `$${Number.parseFloat(value).toLocaleString()}`
    } else {
      cell.textContent = value || "N/A"
    }

    row.appendChild(cell)
  })

  // Add actions cell
  const actionsCell = document.createElement("td")
  actionsCell.innerHTML = `
        <div class="action-buttons">
            <button class="btn btn-warning btn-sm" onclick="editRecord('${entity}', ${record.id})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteFromTable('${entity}', ${record.id})">Delete</button>
        </div>
    `
  row.appendChild(actionsCell)

  return row
}

// Get entity fields for table display
function getEntityFields(entity) {
  const fieldMap = {
    customer: ["name", "email", "phone", "address"],
    artist: ["name", "nationality", "birthdate"],
    room: ["name", "capacity"],
    staff: ["name", "role", "email", "phone"],
    exhibition: ["name", "start_date", "end_date", "room_id", "staff_id"],
    artwork: ["title", "year", "medium", "status", "artist_id", "room_id"],
    ticket: ["customer_id", "exhibition_id", "purchase_date", "price"],
    purchase: ["customer_id", "artwork_id", "purchase_date", "price"],
  }
  return fieldMap[entity] || []
}

// Edit record
function editRecord(entity, id) {
  console.log(`Editing ${entity} record with ID: ${id}`)
  // For now, just switch to form mode - you can implement full edit later
  currentEditingRecord = id
  updateFormMode(entity, "edit")
  switchMode(entity, "form")
}

// Delete record from table
async function deleteFromTable(entity, id) {
  if (confirm(`Are you sure you want to delete this ${entity} record?`)) {
    try {
      const endpoint = getApiEndpoint(entity)
      await apiRequest(`${endpoint}.php`, "DELETE", { id: id })
      alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} deleted successfully!`)
      refreshTable(entity)
    } catch (error) {
      console.error("Delete error:", error)
    }
  }
}

// Search table
function searchTable(tableId, searchTerm) {
  const table = document.getElementById(tableId)
  if (!table) return

  const rows = table.querySelectorAll("tbody tr")

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase()
    const matches = text.includes(searchTerm.toLowerCase())
    row.style.display = matches ? "" : "none"
  })
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  console.log("🎨 Art Gallery Management System initializing...")

  // Set current entity to dashboard
  currentEntity = "dashboard"

  // Load dashboard data
  setTimeout(() => {
    loadDashboardData()
  }, 100)

  console.log("✅ Art Gallery Management System initialized!")
})

// Make functions globally available
window.showTab = showTab
window.switchMode = switchMode
window.saveRecord = saveRecord
window.deleteRecord = deleteRecord
window.editRecord = editRecord
window.deleteFromTable = deleteFromTable
window.refreshTable = refreshTable
window.clearForm = clearForm
window.searchTable = searchTable

console.log("🔧 All functions loaded and available globally")
