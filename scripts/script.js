// Global variables for managing data and state
let currentEditingRecord = null
let currentEntity = null
const dataStorage = {
  customer: [],
  artist: [],
  room: [],
  staff: [],
  exhibition: [],
  artwork: [],
  ticket: [],
  purchase: [],
}

// Tab switching functionality
function showTab(tabName) {
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
  document.getElementById(tabName).classList.add("active")

  // Add active class to clicked nav item
  event.target.classList.add("active")

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

  document.getElementById("page-title").textContent = titles[tabName].title
  document.getElementById("page-description").textContent = titles[tabName].desc

  currentEntity = tabName
}

// Mode switching (Form vs Table view)
function switchMode(entity, mode) {
  const formMode = document.getElementById(`${entity}-form-mode`)
  const tableMode = document.getElementById(`${entity}-table-mode`)
  const modeButtons = document.querySelectorAll(`#${entity} .mode-btn`)

  // Reset mode buttons
  modeButtons.forEach((btn) => btn.classList.remove("active"))

  if (mode === "form") {
    formMode.style.display = "block"
    tableMode.style.display = "none"
    modeButtons[0].classList.add("active")
    clearForm(entity)
  } else {
    formMode.style.display = "none"
    tableMode.style.display = "block"
    modeButtons[1].classList.add("active")
    refreshTable(entity)
  }
}

// Clear form
function clearForm(entity) {
  const form = document.getElementById(`${entity}-form`)
  form.reset()

  // Reset to add mode
  currentEditingRecord = null
  updateFormMode(entity, "add")
}

// Update form mode indicator and buttons
function updateFormMode(entity, mode) {
  const indicator = document.getElementById(`${entity}-mode-indicator`)
  const deleteBtn = document.getElementById(`${entity}-delete-btn`)
  const saveBtn = document.getElementById(`${entity}-save-btn`)

  if (mode === "add") {
    indicator.textContent = `Add New ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
    indicator.className = "form-mode-indicator"
    deleteBtn.style.display = "none"
    saveBtn.textContent = `Save ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
  } else {
    indicator.textContent = `Edit ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
    indicator.className = "form-mode-indicator edit-mode"
    deleteBtn.style.display = "inline-block"
    saveBtn.textContent = `Update ${entity.charAt(0).toUpperCase() + entity.slice(1)}`
  }
}

// Save record (Create or Update)
function saveRecord(entity) {
  const form = document.getElementById(`${entity}-form`)
  const formData = new FormData(form)
  const record = {}

  // Convert FormData to object
  for (const [key, value] of formData.entries()) {
    record[key.replace(`${entity}-`, "")] = value
  }

  if (currentEditingRecord !== null) {
    // Update existing record
    dataStorage[entity][currentEditingRecord] = record
    alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} updated successfully!`)
  } else {
    // Add new record
    dataStorage[entity].push(record)
    alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} added successfully!`)
  }

  // Clear form and refresh table if in table view
  clearForm(entity)
  if (document.getElementById(`${entity}-table-mode`).style.display !== "none") {
    refreshTable(entity)
  }

  console.log(`${entity} data:`, dataStorage[entity])
}

// Delete record
function deleteRecord(entity) {
  if (currentEditingRecord !== null) {
    if (confirm(`Are you sure you want to delete this ${entity} record?`)) {
      dataStorage[entity].splice(currentEditingRecord, 1)
      alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} deleted successfully!`)
      clearForm(entity)
      refreshTable(entity)
    }
  }
}

// Edit record
function editRecord(entity, index) {
  const record = dataStorage[entity][index]
  const form = document.getElementById(`${entity}-form`)

  // Populate form with record data
  Object.keys(record).forEach((key) => {
    const field = document.getElementById(`${entity}-${key}`)
    if (field) {
      field.value = record[key]
    }
  })

  currentEditingRecord = index
  updateFormMode(entity, "edit")

  // Switch to form mode
  switchMode(entity, "form")
}

// Delete record from table
function deleteFromTable(entity, index) {
  if (confirm(`Are you sure you want to delete this ${entity} record?`)) {
    dataStorage[entity].splice(index, 1)
    alert(`${entity.charAt(0).toUpperCase() + entity.slice(1)} deleted successfully!`)
    refreshTable(entity)
  }
}

// Refresh table data
function refreshTable(entity) {
  const table = document.getElementById(`${entity}-table`)
  const tbody = table.querySelector("tbody")

  // Clear existing rows
  tbody.innerHTML = ""

  if (dataStorage[entity].length === 0) {
    tbody.innerHTML = `<tr><td colspan="100%" class="no-data">No ${entity} records found. Add some ${entity}s to see them here.</td></tr>`
    return
  }

  // Add data rows
  dataStorage[entity].forEach((record, index) => {
    const row = createTableRow(entity, record, index)
    tbody.appendChild(row)
  })
}

// Create table row
function createTableRow(entity, record, index) {
  const row = document.createElement("tr")

  // Add data cells based on entity type
  const fields = getEntityFields(entity)
  fields.forEach((field) => {
    const cell = document.createElement("td")
    const value = record[field] || ""

    // Special formatting for certain fields
    if (field === "status" && entity === "artwork") {
      cell.innerHTML = `<span class="status-badge status-${value.toLowerCase().replace(" ", "")}">${value}</span>`
    } else {
      cell.textContent = value
    }

    row.appendChild(cell)
  })

  // Add actions cell
  const actionsCell = document.createElement("td")
  actionsCell.innerHTML = `
        <div class="action-buttons">
            <button class="btn btn-warning btn-sm" onclick="editRecord('${entity}', ${index})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteFromTable('${entity}', ${index})">Delete</button>
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
    exhibition: ["name", "start", "end", "room", "staff"],
    artwork: ["title", "year", "medium", "status", "artist", "room"],
    ticket: ["customer", "exhibition", "purchase-date", "price"],
    purchase: ["customer", "artwork", "date", "price"],
  }
  return fieldMap[entity] || []
}

// Search table
function searchTable(tableId, searchTerm) {
  const table = document.getElementById(tableId)
  const rows = table.querySelectorAll("tbody tr")

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase()
    const matches = text.includes(searchTerm.toLowerCase())
    row.style.display = matches ? "" : "none"
  })
}

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  // Initialize all forms
  const entities = ["customer", "artist", "room", "staff", "exhibition", "artwork", "ticket", "purchase"]
  entities.forEach((entity) => {
    clearForm(entity)
  })

  // Set current entity to dashboard
  currentEntity = "dashboard"

  console.log("Art Gallery Management System initialized")
})
