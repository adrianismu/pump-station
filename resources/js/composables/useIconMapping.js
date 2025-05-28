import { 
  Sun, Cloud, CloudSun, CloudFog, CloudDrizzle, CloudRain, CloudSnow, CloudLightning,
  BookOpen, Video, Clipboard, FileImage
} from "lucide-vue-next"

// Composable untuk mapping icon yang sering digunakan
export function useIconMapping() {
  
  // Icon mapping untuk cuaca
  const getWeatherIcon = (iconName) => {
    const iconMap = {
      'Sun': Sun,
      'Cloud': Cloud,
      'CloudSun': CloudSun,
      'CloudFog': CloudFog,
      'CloudDrizzle': CloudDrizzle,
      'CloudRain': CloudRain,
      'CloudSnow': CloudSnow,
      'CloudLightning': CloudLightning,
    }
    return iconMap[iconName] || Cloud
  }

  // Icon mapping untuk tipe konten edukasi
  const getEducationTypeIcon = (type) => {
    const iconMap = {
      'artikel': BookOpen,
      'video': Video,
      'panduan': Clipboard,
      'infografis': FileImage,
    }
    return iconMap[type] || BookOpen
  }

  return {
    getWeatherIcon,
    getEducationTypeIcon
  }
} 