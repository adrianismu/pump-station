/**
 * Weather Service for internal API integration
 * Uses backend API endpoint that handles caching and Open-Meteo integration
 */

/**
 * Fetch weather data for a specific location (authenticated)
 * @param {number} latitude - Location latitude
 * @param {number} longitude - Location longitude
 * @returns {Promise} - Weather data promise
 */
export async function getWeatherData(latitude, longitude) {
  try {
    const response = await fetch(
      `/api/weather?latitude=${latitude}&longitude=${longitude}`,
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin', // Include cookies for authentication
      }
    )

    if (!response.ok) {
      throw new Error(`Weather API error: ${response.status}`)
    }

    const result = await response.json()
    
    if (!result.success) {
      throw new Error(result.message || 'Failed to fetch weather data')
    }

    return result.data
  } catch (error) {
    console.error("Failed to fetch weather data:", error)
    return null
  }
}

/**
 * Fetch weather data for a specific location (public access)
 * @param {number} latitude - Location latitude
 * @param {number} longitude - Location longitude
 * @returns {Promise} - Weather data promise
 */
export async function getWeatherDataPublic(latitude, longitude) {
  try {
    const response = await fetch(
      `/api/weather/public?latitude=${latitude}&longitude=${longitude}`,
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    )

    if (!response.ok) {
      throw new Error(`Weather API error: ${response.status}`)
    }

    const result = await response.json()
    
    if (!result.success) {
      throw new Error(result.message || 'Failed to fetch weather data')
    }

    return result.data
  } catch (error) {
    console.error("Failed to fetch weather data:", error)
    return null
  }
}

/**
 * Fetch weather data for a specific pump house
 * @param {number} pumpHouseId - Pump house ID
 * @returns {Promise} - Weather data promise
 */
export async function getWeatherDataForPumpHouse(pumpHouseId) {
  try {
    const response = await fetch(
      `/api/weather/pump-house/${pumpHouseId}`,
      {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      }
    )

    if (!response.ok) {
      throw new Error(`Weather API error: ${response.status}`)
    }

    const result = await response.json()
    
    if (!result.success) {
      throw new Error(result.message || 'Failed to fetch weather data')
    }

    return result.data
  } catch (error) {
    console.error("Failed to fetch weather data for pump house:", error)
    return null
  }
}

/**
 * Get weather code description
 * @param {number} code - Weather code from Open-Meteo
 * @returns {string} - Weather description
 * Note: This is now mainly used for fallback. The backend provides descriptions directly.
 */
export function getWeatherDescription(code) {
  const weatherCodes = {
    0: "Cerah",
    1: "Sebagian Berawan",
    2: "Berawan",
    3: "Mendung",
    45: "Kabut",
    48: "Kabut Beku",
    51: "Gerimis Ringan",
    53: "Gerimis Sedang",
    55: "Gerimis Lebat",
    56: "Gerimis Beku Ringan",
    57: "Gerimis Beku Lebat",
    61: "Hujan Ringan",
    63: "Hujan Sedang",
    65: "Hujan Lebat",
    66: "Hujan Beku Ringan",
    67: "Hujan Beku Lebat",
    71: "Salju Ringan",
    73: "Salju Sedang",
    75: "Salju Lebat",
    77: "Butiran Salju",
    80: "Hujan Ringan",
    81: "Hujan Sedang",
    82: "Hujan Sangat Lebat",
    85: "Hujan Salju Ringan",
    86: "Hujan Salju Lebat",
    95: "Badai Petir",
    96: "Badai Petir dengan Hujan Es Ringan",
    99: "Badai Petir dengan Hujan Es Lebat",
  }

  return weatherCodes[code] || "Tidak Diketahui"
}

/**
 * Format rainfall amount with appropriate units
 * @param {number} amount - Rainfall amount in mm
 * @returns {string} - Formatted rainfall string
 */
export function formatRainfall(amount) {
  if (amount === 0) return "0 mm"
  if (amount < 0.1) return "< 0.1 mm"
  return `${amount.toFixed(1)} mm`
}

/**
 * Get rainfall intensity description
 * @param {number} amount - Rainfall amount in mm
 * @returns {string} - Intensity description
 */
export function getRainfallIntensity(amount) {
  if (amount === 0) return "Tidak ada hujan"
  if (amount < 0.5) return "Sangat ringan"
  if (amount < 4) return "Ringan"
  if (amount < 10) return "Sedang"
  if (amount < 20) return "Lebat"
  return "Sangat lebat"
}

/**
 * Get weather icon based on weather code
 * @param {number} code - Weather code from Open-Meteo
 * @returns {string} - Lucide icon name
 * Note: This is now mainly used for fallback. The backend provides icon names directly.
 */
export function getWeatherIcon(code) {
  if (code === 0) return "Sun"
  if (code === 1) return "Cloud"
  if ([2, 3].includes(code)) return "CloudSun"
  if ([45, 48].includes(code)) return "CloudFog"
  if ([51, 53, 55, 56, 57].includes(code)) return "CloudDrizzle"
  if ([61, 63, 65, 66, 67, 80, 81, 82].includes(code)) return "CloudRain"
  if ([71, 73, 75, 77, 85, 86].includes(code)) return "CloudSnow"
  if ([95, 96, 99].includes(code)) return "CloudLightning"
  return "Cloud"
}

/**
 * Calculate flood risk based on rainfall and weather conditions
 * @param {number} rainfall - Rainfall amount in mm
 * @param {number} weatherCode - Weather code from Open-Meteo
 * @returns {string} - Flood risk level: 'Tinggi', 'Sedang', or 'Rendah'
 */
export function calculateFloodRisk(rainfall, weatherCode) {
  // High risk: Heavy rainfall or severe weather conditions
  if (rainfall > 20 || [95, 96, 99].includes(weatherCode)) return 'Tinggi'
  
  // Medium risk: Moderate rainfall or heavy rain weather codes
  if (rainfall > 10 || [80, 81, 82].includes(weatherCode)) return 'Sedang'
  
  // Low risk: Light or no rainfall
  return 'Rendah'
}

/**
 * Get flood risk badge variant for UI styling
 * @param {string} risk - Risk level: 'Tinggi', 'Sedang', or 'Rendah'
 * @returns {string} - Badge variant: 'destructive', 'warning', or 'default'
 */
export function getFloodRiskVariant(risk) {
  if (risk === 'Tinggi') return 'destructive'
  if (risk === 'Sedang') return 'warning'
  return 'default'
}
