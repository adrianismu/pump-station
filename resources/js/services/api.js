import axios from "axios"

// Set up CSRF token for all requests
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
  axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content
} else {
  console.error("CSRF token not found")
}

const api = {
  // Dashboard
  getDashboardData() {
    return axios.get("/api/dashboard")
  },

  // Pump Houses
  getPumpHouses() {
    return axios.get("/api/pump-houses")
  },

  getPumpHouse(id) {
    return axios.get(`/api/pump-houses/${id}`)
  },

  createPumpHouse(data) {
    return axios.post("/api/pump-houses", data)
  },

  updatePumpHouse(id, data) {
    return axios.put(`/api/pump-houses/${id}`, data)
  },

  deletePumpHouse(id) {
    return axios.delete(`/api/pump-houses/${id}`)
  },
}

export default api
