# DOKUMENTASI SKRIPSI LENGKAP - BAGIAN 2
# BAB IV - VIII

## BAB IV: ANALISIS DAN PERANCANGAN SISTEM

### 4.1 Analisis Kebutuhan Sistem

#### 4.1.1 Analisis Stakeholder

**Primary Stakeholders**:

1. **Administrator Sistem**
   - **Role**: Pengelola utama sistem dengan akses penuh
   - **Responsibilities**: Manajemen user, konfigurasi sistem, monitoring overall
   - **Requirements**: Dashboard comprehensive, user management, system configuration
   - **Pain Points**: Keterbatasan akses real-time, manual report generation

2. **Petugas Lapangan**
   - **Role**: Input data ketinggian air dan response laporan
   - **Responsibilities**: Data entry, field verification, incident response
   - **Requirements**: Mobile-friendly interface, quick data input, offline capability
   - **Pain Points**: Interface tidak mobile-friendly, proses input lambat

3. **Masyarakat Umum**
   - **Role**: Konsumen informasi dan reporter masalah
   - **Responsibilities**: View public information, submit reports
   - **Requirements**: User-friendly interface, easy reporting, status tracking
   - **Pain Points**: Tidak ada channel pelaporan, informasi tidak accessible

**Secondary Stakeholders**:
- Dinas PU Pengairan Surabaya
- BMKG untuk data cuaca
- BPBD untuk emergency response
- Developer dan maintainer sistem

#### 4.1.2 Analisis Kebutuhan Fungsional

**RF-001: Authentication & Authorization**
- **Description**: Sistem login dengan role-based access control
- **Actor**: All users
- **Precondition**: User memiliki valid credentials
- **Flow**: 
  1. User input email/password
  2. System validate credentials
  3. System generate session token
  4. Redirect based on user role
- **Postcondition**: User authenticated dengan appropriate permissions

**RF-002: Real-time Dashboard**
- **Description**: Dashboard monitoring real-time semua rumah pompa
- **Actor**: Admin, Petugas
- **Precondition**: User authenticated
- **Flow**:
  1. Load pump house data with current status
  2. Fetch latest water level readings
  3. Get weather data from external API
  4. Calculate risk levels
  5. Display with charts and maps
- **Postcondition**: Current system status displayed

**RF-003: Water Level Management**
- **Description**: Input dan monitoring data ketinggian air
- **Actor**: Petugas
- **Precondition**: User has petugas role
- **Flow**:
  1. Select pump house
  2. Input water level value
  3. Add timestamp and notes
  4. Validate against thresholds
  5. Trigger alerts if necessary
- **Postcondition**: Water level data stored and alerts generated

**RF-004: Alert System**
- **Description**: Sistem peringatan otomatis berdasarkan threshold
- **Actor**: System (automated)
- **Precondition**: Water level data available
- **Flow**:
  1. Check water level against thresholds
  2. Determine alert level (Normal/Warning/Critical)
  3. Generate alert message
  4. Send notifications to relevant users
  5. Log alert actions
- **Postcondition**: Appropriate alerts generated and sent

**RF-005: Interactive Map**
- **Description**: Peta interaktif dengan lokasi dan status rumah pompa
- **Actor**: All users
- **Precondition**: Location data available
- **Flow**:
  1. Load base map (OpenStreetMap)
  2. Plot pump house locations as markers
  3. Color-code markers by status
  4. Add popup with detailed information
  5. Enable clustering for performance
- **Postcondition**: Interactive map displayed with current data

**RF-006: Public Reporting System**
- **Description**: Platform pelaporan masalah oleh masyarakat
- **Actor**: Public users
- **Precondition**: None (anonymous allowed)
- **Flow**:
  1. User access report form
  2. Fill report details (title, description, location)
  3. Upload supporting images
  4. Submit report
  5. Generate tracking number
  6. Send confirmation
- **Postcondition**: Report submitted and tracking number provided

**RF-007: Report Management**
- **Description**: Manajemen laporan dan response oleh admin/petugas
- **Actor**: Admin, Petugas
- **Precondition**: Reports exist in system
- **Flow**:
  1. View list of reports with filters
  2. Select report for review
  3. Update status and add response
  4. Assign to team member if needed
  5. Close report when resolved
- **Postcondition**: Report status updated and response documented

**RF-008: Educational Content**
- **Description**: Platform konten edukasi pencegahan banjir
- **Actor**: All users
- **Precondition**: Content available in system
- **Flow**:
  1. Browse content categories
  2. Search content by keywords
  3. View articles/videos/infographics
  4. Download materials
  5. Rate and comment content
- **Postcondition**: Educational content accessed

**RF-009: Weather Integration**
- **Description**: Integrasi data cuaca untuk analisis risiko
- **Actor**: System (automated)
- **Precondition**: External API available
- **Flow**:
  1. Fetch weather data from Open-Meteo API
  2. Process and store relevant parameters
  3. Correlate with water level data
  4. Generate risk assessment
  5. Update dashboard displays
- **Postcondition**: Weather data integrated and risk calculated

**RF-010: Data Export & Reporting**
- **Description**: Export data dalam various formats untuk analysis
- **Actor**: Admin, Petugas
- **Precondition**: Data available in system
- **Flow**:
  1. Select data range and parameters
  2. Choose export format (Excel, PDF, CSV)
  3. Generate report
  4. Download file
- **Postcondition**: Data exported in requested format

#### 4.1.3 Analisis Kebutuhan Non-Fungsional

**NFR-001: Performance Requirements**
- **Response Time**: 
  - Page load: ≤ 3 seconds
  - API calls: ≤ 500ms
  - Database queries: ≤ 100ms
- **Throughput**: Support 100 concurrent users
- **Scalability**: Handle 10x data growth without performance degradation

**NFR-002: Security Requirements**
- **Authentication**: Multi-factor authentication untuk admin
- **Authorization**: Role-based access control dengan principle of least privilege
- **Data Protection**: 
  - Encryption in transit (HTTPS/TLS 1.3)
  - Encryption at rest for sensitive data
  - Input validation dan sanitization
- **Audit Trail**: Comprehensive logging untuk security events

**NFR-003: Reliability Requirements**
- **Availability**: 99% uptime (max 7.2 hours downtime per month)
- **Fault Tolerance**: Graceful degradation when external APIs unavailable
- **Backup & Recovery**: 
  - Daily automated backups
  - RTO (Recovery Time Objective): 4 hours
  - RPO (Recovery Point Objective): 1 hour

**NFR-004: Usability Requirements**
- **User Interface**: Intuitive design dengan max 3 clicks untuk common tasks
- **Responsive Design**: Support desktop, tablet, mobile (min 320px width)
- **Accessibility**: WCAG 2.1 AA compliance
- **Internationalization**: Support Bahasa Indonesia dan English

**NFR-005: Compatibility Requirements**
- **Browser Support**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Mobile Compatibility**: iOS 12+, Android 8+
- **API Compatibility**: RESTful API dengan versioning

**NFR-006: Maintainability Requirements**
- **Code Quality**: 
  - PSR-12 compliance untuk PHP
  - ESLint compliance untuk JavaScript
  - Minimum 80% code coverage
- **Documentation**: Comprehensive technical documentation
- **Modularity**: Loosely coupled components untuk easy maintenance

### 4.2 Perancangan Arsitektur Sistem

#### 4.2.1 Arsitektur Umum Sistem

**High-Level Architecture**:
```
┌─────────────────────────────────────────────────────────────────┐
│                        Presentation Layer                       │
├─────────────────┬───────────────────┬───────────────────────────┤
│   Web Browser   │   Mobile Browser  │     Admin Dashboard       │
│   (Vue.js 3)    │   (Responsive)    │     (Advanced Features)   │
└─────────────────┴───────────────────┴───────────────────────────┘
                                │
                ┌───────────────────────────────┐
                │        API Gateway           │
                │      (Laravel Routes)        │
                └───────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                     Application Layer                           │
├─────────────────┬───────────────────┬───────────────────────────┤
│   Controllers   │     Services      │      Middleware           │
│   (HTTP Logic)  │ (Business Logic)  │   (Auth, CORS, etc)       │
└─────────────────┴───────────────────┴───────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                       Data Layer                                │
├─────────────────┬───────────────────┬───────────────────────────┤
│   MySQL DB      │     Redis Cache   │    File Storage           │
│ (Primary Data)  │   (Sessions,      │  (Images, Documents)      │
│                 │    Cache)         │                           │
└─────────────────┴───────────────────┴───────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────────┐
│                    External Services                            │
├─────────────────┬───────────────────┬───────────────────────────┤
│  Open-Meteo     │   Map Tiles       │      Notification         │
│  Weather API    │ (OpenStreetMap)   │     Services              │
└─────────────────┴───────────────────┴───────────────────────────┘
```

#### 4.2.2 Component Architecture

**Backend Components (Laravel)**:

1. **Controllers Layer**:
   ```php
   app/Http/Controllers/
   ├── Admin/
   │   ├── DashboardController.php
   │   ├── PumpHouseController.php
   │   ├── UserController.php
   │   └── ReportController.php
   ├── API/
   │   ├── WaterLevelController.php
   │   ├── WeatherController.php
   │   └── AlertController.php
   └── Auth/
       ├── LoginController.php
       └── RegisterController.php
   ```

2. **Services Layer**:
   ```php
   app/Services/
   ├── WeatherService.php
   ├── AlertService.php
   ├── NotificationService.php
   ├── ReportService.php
   └── DataExportService.php
   ```

3. **Models Layer**:
   ```php
   app/Models/
   ├── PumpHouse.php
   ├── WaterLevelHistory.php
   ├── Alert.php
   ├── Report.php
   ├── User.php
   └── EducationContent.php
   ```

**Frontend Components (Vue.js)**:

1. **Layout Components**:
   ```
   resources/js/Layouts/
   ├── AdminLayout.vue
   ├── GuestLayout.vue
   └── AuthenticatedLayout.vue
   ```

2. **Page Components**:
   ```
   resources/js/Pages/
   ├── Admin/
   │   ├── Dashboard.vue
   │   ├── PumpHouses/
   │   ├── Reports/
   │   └── Users/
   ├── Public/
   │   ├── Home.vue
   │   ├── Map.vue
   │   └── Education/
   └── Auth/
       ├── Login.vue
       └── Register.vue
   ```

3. **Reusable Components**:
   ```
   resources/js/Components/
   ├── ui/           (Shadcn/ui components)
   ├── Charts/       (Chart.js wrappers)
   ├── Map/          (Leaflet components)
   └── Forms/        (Form components)
   ```

#### 4.2.3 Data Flow Architecture

**Real-time Data Flow**:
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Sensor/   │    │   Backend   │    │   Cache     │    │  Frontend   │
│   Manual    │───▶│  Processing │───▶│   Layer     │───▶│  Dashboard  │
│   Input     │    │   (Laravel) │    │   (Redis)   │    │  (Vue.js)   │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │                   │
       │                   ▼                   │                   │
       │            ┌─────────────┐            │                   │
       │            │  Database   │            │                   │
       └───────────▶│   (MySQL)   │◄───────────┘                   │
                    └─────────────┘                                │
                           │                                       │
                           ▼                                       │
                    ┌─────────────┐                                │
                    │   Alert     │                                │
                    │  Generation │───────────────────────────────┘
                    └─────────────┘
```

**API Integration Flow**:
```
External APIs ──┐
                │
┌─────────────┐ │    ┌─────────────┐    ┌─────────────┐
│ Open-Meteo  │─┼───▶│   Service   │───▶│   Cache     │
│ Weather API │ │    │   Layer     │    │  (1 hour)   │
└─────────────┘ │    └─────────────┘    └─────────────┘
                │           │                   │
┌─────────────┐ │           ▼                   ▼
│   Map Tiles │─┘    ┌─────────────┐    ┌─────────────┐
│(OpenStreetMap)     │  Dashboard  │◄───│   Frontend  │
└─────────────┘      │ Integration │    │Components   │
                     └─────────────┘    └─────────────┘
```

### 4.3 Perancangan Database

#### 4.3.1 Entity Relationship Diagram (ERD)

**Conceptual ERD**:
```
┌─────────────┐                    ┌─────────────┐
│    Users    │                    │ PumpHouses  │
│─────────────│                    │─────────────│
│ id (PK)     │───────────────┐    │ id (PK)     │
│ name        │               │    │ name        │
│ email       │               │    │ address     │
│ role        │               │    │ lat         │
│ created_at  │               │    │ lng         │
└─────────────┘               │    │ status      │
                              │    │ capacity    │
                              │    └─────────────┘
                              │           │
                              │           │ 1:N
                              │           ▼
┌─────────────┐               │    ┌─────────────┐
│   Reports   │               │    │WaterLevel   │
│─────────────│               │    │  History    │
│ id (PK)     │               │    │─────────────│
│ title       │               │    │ id (PK)     │
│ description │         ┌─────────│ pump_house_id(FK)
│ location    │         │         │ water_level │
│ status      │         │         │ recorded_at │
│ images      │         │         │ weather_condition
│ created_by  │─────────┘         └─────────────┘
└─────────────┘                          │
       │                                 │ 1:N
       │ 1:N                             ▼
       ▼                          ┌─────────────┐
┌─────────────┐                   │   Alerts    │
│   Report    │                   │─────────────│
│ Responses   │                   │ id (PK)     │
│─────────────│                   │ pump_house_id(FK)
│ id (PK)     │                   │ level       │
│ report_id(FK)                   │ message     │
│ response    │                   │ is_active   │
│ status      │                   │ created_at  │
│ created_by  │                   └─────────────┘
└─────────────┘                          │
                                         │ 1:N
                                         ▼
                                  ┌─────────────┐
                                  │AlertActions │
                                  │─────────────│
                                  │ id (PK)     │
                                  │ alert_id(FK)│
                                  │ action_taken│
                                  │ taken_by    │
                                  │ taken_at    │
                                  └─────────────┘
```

#### 4.3.2 Database Schema Detail

**Table: users**
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'petugas', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);
```

**Table: pump_houses**
```sql
CREATE TABLE pump_houses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    lat DECIMAL(10, 7) NOT NULL,
    lng DECIMAL(10, 7) NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    capacity VARCHAR(50) NOT NULL COMMENT 'Capacity in m³/s',
    pump_count INTEGER NOT NULL DEFAULT 1,
    built_year YEAR,
    manager_name VARCHAR(255),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(255),
    staff_count INTEGER DEFAULT 0,
    image VARCHAR(255),
    description TEXT,
    last_updated TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_location (lat, lng),
    INDEX idx_name (name)
);
```

**Table: water_level_history**
```sql
CREATE TABLE water_level_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pump_house_id BIGINT UNSIGNED NOT NULL,
    water_level DECIMAL(5, 2) NOT NULL COMMENT 'Water level in meters',
    recorded_at TIMESTAMP NOT NULL,
    weather_condition VARCHAR(100),
    temperature DECIMAL(4, 1),
    humidity DECIMAL(5, 2),
    rainfall DECIMAL(6, 2),
    notes TEXT,
    recorded_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pump_house_id) REFERENCES pump_houses(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pump_house_time (pump_house_id, recorded_at),
    INDEX idx_water_level (water_level),
    INDEX idx_recorded_at (recorded_at)
);
```

**Table: alerts**
```sql
CREATE TABLE alerts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pump_house_id BIGINT UNSIGNED NOT NULL,
    level ENUM('normal', 'warning', 'critical') NOT NULL,
    message TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    water_level DECIMAL(5, 2),
    weather_data JSON,
    severity_score INTEGER DEFAULT 0,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    resolved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pump_house_id) REFERENCES pump_houses(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pump_house_level (pump_house_id, level),
    INDEX idx_active_alerts (is_active, created_at),
    INDEX idx_severity (severity_score)
);
```

**Table: reports**
```sql
CREATE TABLE reports (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    lat DECIMAL(10, 7),
    lng DECIMAL(10, 7),
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    category VARCHAR(100),
    reporter_name VARCHAR(255),
    reporter_phone VARCHAR(20),
    reporter_email VARCHAR(255),
    images JSON COMMENT 'Array of image URLs',
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status_priority (status, priority),
    INDEX idx_location (lat, lng),
    INDEX idx_assigned (assigned_to),
    INDEX idx_created_at (created_at)
);
```

#### 4.3.3 Database Optimization Strategy

**Indexing Strategy**:
1. **Primary Keys**: Auto-increment BIGINT untuk semua tables
2. **Foreign Keys**: Indexed untuk optimal JOIN performance
3. **Query-based Indexes**: Berdasarkan frequent query patterns
4. **Composite Indexes**: Untuk multi-column WHERE clauses

**Partitioning Strategy**:
```sql
-- Partition water_level_history by month for better performance
ALTER TABLE water_level_history 
PARTITION BY RANGE (YEAR(recorded_at) * 100 + MONTH(recorded_at)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    -- ... monthly partitions
    PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

**Data Archiving Strategy**:
- Water level data older than 2 years moved to archive tables
- Alert data older than 1 year archived
- Report data archived after 3 years

### 4.4 Perancangan Interface

#### 4.4.1 Design System

**Color Palette**:
```css
:root {
  /* Primary Colors - Blue theme for water */
  --primary-50: #eff6ff;
  --primary-100: #dbeafe;
  --primary-500: #3b82f6;
  --primary-600: #2563eb;
  --primary-700: #1d4ed8;
  
  /* Secondary Colors */
  --secondary-50: #f8fafc;
  --secondary-100: #f1f5f9;
  --secondary-500: #64748b;
  --secondary-600: #475569;
  
  /* Status Colors */
  --success: #10b981;    /* Normal status */
  --warning: #f59e0b;    /* Warning level */
  --danger: #ef4444;     /* Critical level */
  --info: #06b6d4;       /* Information */
  
  /* Neutral Colors */
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-900: #111827;
}
```

**Typography System**:
```css
/* Font Family */
--font-sans: 'Inter', system-ui, sans-serif;
--font-mono: 'JetBrains Mono', monospace;

/* Font Sizes */
--text-xs: 0.75rem;     /* 12px */
--text-sm: 0.875rem;    /* 14px */
--text-base: 1rem;      /* 16px */
--text-lg: 1.125rem;    /* 18px */
--text-xl: 1.25rem;     /* 20px */
--text-2xl: 1.5rem;     /* 24px */
--text-3xl: 1.875rem;   /* 30px */
--text-4xl: 2.25rem;    /* 36px */

/* Font Weights */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

**Spacing System** (Tailwind CSS):
```css
/* Consistent spacing scale */
--space-1: 0.25rem;    /* 4px */
--space-2: 0.5rem;     /* 8px */
--space-4: 1rem;       /* 16px */
--space-6: 1.5rem;     /* 24px */
--space-8: 2rem;       /* 32px */
--space-12: 3rem;      /* 48px */
--space-16: 4rem;      /* 64px */
```

#### 4.4.2 Component Design System

**Base Components (Shadcn/ui)**:

1. **Button Component**:
```vue
<template>
  <button
    :class="buttonClasses"
    :disabled="disabled"
    @click="$emit('click', $event)"
  >
    <slot />
  </button>
</template>

<script>
export default {
  props: {
    variant: {
      type: String,
      default: 'primary',
      validator: value => ['primary', 'secondary', 'success', 'warning', 'danger'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: value => ['sm', 'md', 'lg'].includes(value)
    },
    disabled: Boolean
  },
  computed: {
    buttonClasses() {
      return [
        'inline-flex items-center justify-center rounded-md font-medium transition-colors',
        `btn-${this.variant}`,
        `btn-${this.size}`,
        { 'opacity-50 cursor-not-allowed': this.disabled }
      ]
    }
  }
}
</script>
```

2. **Card Component**:
```vue
<template>
  <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
    <div v-if="$slots.header" class="px-6 py-4 border-b border-gray-200">
      <slot name="header" />
    </div>
    <div class="px-6 py-4">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200">
      <slot name="footer" />
    </div>
  </div>
</template>
```

#### 4.4.3 Layout Design

**Responsive Grid System**:
```css
/* Desktop Layout (>=1024px) */
.admin-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  grid-template-rows: 60px 1fr;
  grid-template-areas:
    "sidebar header"
    "sidebar main";
  min-height: 100vh;
}

/* Tablet Layout (768px - 1023px) */
@media (max-width: 1023px) {
  .admin-layout {
    grid-template-columns: 1fr;
    grid-template-areas:
      "header"
      "main";
  }
  
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.open {
    transform: translateX(0);
  }
}

/* Mobile Layout (<768px) */
@media (max-width: 767px) {
  .admin-layout {
    padding: 0;
  }
  
  .main-content {
    padding: 1rem;
  }
}
```

**Dashboard Layout Structure**:
```vue
<template>
  <div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar bg-gray-900 text-white">
      <nav class="mt-5 px-2">
        <NavigationMenu :items="menuItems" />
      </nav>
    </aside>
    
    <!-- Header -->
    <header class="header bg-white border-b border-gray-200">
      <div class="flex items-center justify-between px-4 py-3">
        <h1 class="text-xl font-semibold">{{ pageTitle }}</h1>
        <UserMenu />
      </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content p-6">
      <slot />
    </main>
  </div>
</template>
```

#### 4.4.4 User Interface Specifications

**Dashboard Interface**:

1. **Overview Section**:
   - KPI cards: Total Pumps, Active Pumps, Critical Alerts, Reports Today
   - Real-time status indicators dengan color coding
   - Quick action buttons

2. **Charts Section**:
   - Water level trends (line chart) - 7 days
   - Alert distribution (donut chart) - by severity
   - Weather correlation (bar chart)
   - Pump house status (map visualization)

3. **Alerts Section**:
   - Real-time alert feed dengan auto-refresh
   - Filter by severity dan pump house
   - Quick acknowledge/resolve actions

**Map Interface**:

1. **Map Container**:
   - Full-screen map dengan Leaflet.js
   - Cluster markers untuk performance
   - Custom icons based on pump status

2. **Map Controls**:
   - Zoom controls
   - Layer switcher (satellite/street view)
   - Search/filter pump houses
   - Legend untuk status indicators

3. **Popup Information**:
   - Pump house basic info
   - Current water level
   - Recent alerts
   - Quick actions (view details, add report)

**Mobile Interface Adaptations**:

1. **Navigation**:
   - Bottom tab navigation untuk main sections
   - Hamburger menu untuk secondary features
   - Swipe gestures untuk navigation

2. **Data Entry**:
   - Large touch targets (min 44px)
   - Optimized forms dengan appropriate input types
   - Camera integration untuk image upload

3. **Charts & Maps**:
   - Touch-optimized zoom dan pan
   - Simplified chart interfaces
   - GPS integration untuk location input

---

*[Dokumentasi akan dilanjutkan dengan BAB V-VIII dalam file terpisah]* 