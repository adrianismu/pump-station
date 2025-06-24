<template>
  <SidebarProvider>
    <Sidebar variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
              <div class="flex items-center gap-2">
                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                  <Droplet class="size-4" />
                </div>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">Rumah Pompa</span>
                  <span class="truncate text-xs">Monitoring System</span>
                </div>
              </div>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <SidebarGroup v-for="section in navigationSections" :key="section.title">
          <SidebarGroupLabel>{{ section.title }}</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem v-for="item in section.items" :key="item.route">
                <SidebarMenuButton 
                  :as-child="true"
                  :data-active="$page.component === item.component"
                >
                  <Link :href="route(item.route)">
                    <component :is="item.icon" />
                    <span>{{ item.label }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <SidebarMenuButton
                  size="lg"
                  class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                >
                  <img 
                    :src="userAvatar" 
                    :alt="userName" 
                    class="size-8 rounded-full" 
                  />
                  <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">{{ userName }}</span>
                    <span class="truncate text-xs">{{ userEmail }}</span>
                  </div>
                  <ChevronDown class="ml-auto size-4" />
                </SidebarMenuButton>
              </DropdownMenuTrigger>
              <DropdownMenuContent
                class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                side="bottom"
                align="end"
                :side-offset="4"
              >
                <DropdownMenuLabel class="p-0 font-normal">
                  <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <img 
                      :src="userAvatar" 
                      :alt="userName" 
                      class="size-8 rounded-full" 
                    />
                    <div class="grid flex-1 text-left text-sm leading-tight">
                      <span class="truncate font-semibold">{{ userName }}</span>
                      <span class="truncate text-xs">{{ userEmail }}</span>
                    </div>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem as-child>
                  <Link :href="route('profile.edit')">
                    <User />
                    Edit Profil
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem as-child>
                  <Link :href="route('admin.threshold-settings.index')">
                    <Settings />
                    Pengaturan Sistem
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem as-child>
                  <Link :href="route('logout')" method="post" as="button">
                    <LogOut />
                    Keluar
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
    </Sidebar>

    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />
          <Breadcrumb>
            <BreadcrumbList>
              <BreadcrumbItem class="hidden md:block">
                <BreadcrumbLink href="#">
                  Dashboard
                </BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>{{ currentPageTitle }}</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
        
        <div class="ml-auto flex items-center gap-2 px-4">
          <!-- Theme Toggle -->
          <ThemeToggle />
          
          <!-- Notifications - Untuk admin dan petugas -->
          <div class="relative">
            <button 
              @click="toggleNotifications" 
              class="relative inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10"
            >
              <Bell class="h-4 w-4" />
              <span 
                v-if="unreadNotificationsCount > 0" 
                class="absolute -top-1 -right-1 bg-destructive text-destructive-foreground text-xs rounded-full w-5 h-5 flex items-center justify-center"
              >
                {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
              </span>
            </button>
            
            <!-- Notification dropdown -->
            <div 
              v-if="showNotifications" 
              class="absolute right-0 mt-2 w-80 bg-card border border-border rounded-md shadow-lg z-10"
            >
              <div class="p-3 border-b border-border flex justify-between items-center">
                <h3 class="font-semibold">Notifikasi</h3>
                <button 
                  v-if="unreadNotificationsCount > 0"
                  @click="markAllAsRead" 
                  class="text-xs text-primary hover:underline"
                >
                  Tandai semua dibaca
                </button>
              </div>
              <div class="max-h-96 overflow-y-auto custom-scrollbar">
                <div v-if="isLoadingNotifications" class="p-4 text-center">
                  <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                  <p class="mt-2 text-sm text-muted-foreground">Memuat notifikasi...</p>
                </div>
                <div v-else-if="notifications.length === 0" class="p-4 text-center">
                  <Bell class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
                  <p class="text-sm text-muted-foreground">Tidak ada notifikasi</p>
                </div>
                <div 
                  v-else
                  v-for="notification in notifications" 
                  :key="notification.id" 
                  class="p-3 border-b border-border hover:bg-muted cursor-pointer"
                  @click="viewNotification(notification)"
                >
                  <div class="flex items-start gap-3">
                    <!-- Weather Alert Icon -->
                    <CloudRain 
                      v-if="notification.type === 'weather_forecast'"
                      class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" 
                    />
                    <!-- Water Level Alert Icon -->
                    <Droplet 
                      v-else-if="notification.type === 'water_level'"
                      class="w-5 h-5 text-cyan-500 mt-0.5 flex-shrink-0" 
                    />

                    <!-- Warning Icon -->
                    <AlertCircle 
                      v-else-if="notification.type === 'warning'" 
                      class="w-5 h-5 text-warning mt-0.5 flex-shrink-0" 
                    />
                    <!-- Default Info Icon -->
                    <Info v-else class="w-5 h-5 text-info mt-0.5 flex-shrink-0" />
                    
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <p class="font-medium text-sm truncate">{{ notification.title }}</p>
                        
                        <!-- Weather Alert Badge -->
                        <span 
                          v-if="notification.type === 'weather_forecast'"
                          class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 flex-shrink-0 ml-2"
                        >
                          Cuaca
                        </span>
                        
                        <!-- Water Level Alert Badge -->
                        <span 
                          v-else-if="notification.type === 'water_level'"
                          class="text-xs px-2 py-1 rounded-full bg-cyan-100 text-cyan-800 flex-shrink-0 ml-2"
                        >
                          Level Air
                        </span>
                        
                        <!-- Severity Badge for Important Notifications -->
                        <span 
                          v-if="['critical', 'high', 'medium'].includes(notification.severity)"
                          class="text-xs px-2 py-1 rounded-full text-white flex-shrink-0 ml-2"
                          :style="{ backgroundColor: notification.color }"
                        >
                          {{ getSeverityLabel(notification.severity) }}
                        </span>
                        

                      </div>
                      <p class="text-sm text-muted-foreground">{{ notification.message }}</p>
                      <div class="flex items-center justify-between mt-1">
                        <p class="text-xs text-muted-foreground">{{ notification.time_ago }}</p>
                        <div class="flex items-center gap-2">

                          

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="p-2 border-t border-border">
                <Link :href="route('admin.notifications')" class="block w-full text-center text-sm text-primary py-1 hover:underline">
                  Lihat Semua Notifikasi
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="flex flex-1 flex-col gap-4 p-4 pt-0 custom-scrollbar">
        <slot />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  Bell, 
  AlertCircle, 
  Info,
  CloudRain,
  Droplet,
  User,
  Settings,
  LogOut,
  BadgeAlert,
  ChevronDown,
  MapPin,
  Activity,
  BarChart3,
  Database,
  GraduationCap
} from 'lucide-vue-next';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// Sidebar components
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarTrigger,
} from '@/Components/ui/sidebar';

// Other UI components
import { Separator } from '@/Components/ui/separator';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

// Custom components
import ThemeToggle from '@/Components/ThemeToggle.vue';

// Configure axios to include CSRF token for API requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Get authenticated user from Inertia shared props
const page = usePage();

// User information
const userName = computed(() => {
  return page.props.auth?.user?.name || 'Admin User';
});

const userEmail = computed(() => {
  return page.props.auth?.user?.email || 'admin@rumahpompa.id';
});

const userAvatar = computed(() => {
  return page.props.auth?.user?.avatar || 'https://ui.shadcn.com/avatars/01.png';
});

const userRole = computed(() => {
  return page.props.auth?.user?.role || 'admin';
});

const isAdmin = computed(() => {
  return userRole.value === 'admin';
});

// Current page title
const currentPageTitle = computed(() => {
  const component = page.component;
  const titleMap = {
    'Admin/Dashboard': 'Dashboard',
    'Admin/WaterLevel/Index': 'Input Ketinggian Air',
    'Admin/Map': 'Peta Monitoring',
    'Admin/Database/Index': 'Database Rumah Pompa',
    'Admin/Database/Show': 'Detail Rumah Pompa',
    'Admin/Reports/Index': 'Laporan Publik',
    'Admin/Notifications/Index': 'Notifikasi & Alert',
    'Admin/ThresholdSettings/Index': 'Pengaturan Threshold Global',
    'Admin/PumpHouseThreshold/Index': 'Threshold per Lokasi',
    'Admin/Education/Index': 'Manajemen Konten Edukasi',
    'Admin/UserPumpHouse/Index': 'Manajemen Akses Petugas',
    'Admin/UserPumpHouse/Show': 'Detail Akses Petugas'
  };
  return titleMap[component] || 'Dashboard';
});

// Navigation items with sections - filtered by role
const navigationSections = computed(() => {
  const allSections = [
    {
      title: 'Overview',
      items: [
        { 
          label: 'Dashboard', 
          icon: LayoutDashboard, 
          route: 'admin.dashboard', 
          component: 'Dashboard',
          roles: ['admin', 'petugas']
        },
        { 
          label: 'Peta Monitoring', 
          icon: MapPin, 
          route: 'admin.map', 
          component: 'Map',
          roles: ['admin', 'petugas']
        }
      ]
    },
    {
      title: 'Monitoring & Data',
      items: [
        { 
          label: 'Input Ketinggian Air', 
          icon: Activity, 
          route: 'admin.water-level.index', 
          component: 'WaterLevel/Index',
          roles: ['admin', 'petugas']
        },
        { 
          label: 'Database Rumah Pompa', 
          icon: Database, 
          route: 'admin.database', 
          component: 'Database',
          roles: ['admin']
        }
      ]
    },
    {
      title: 'Laporan & Notifikasi',
      items: [
        { 
          label: 'Laporan Publik', 
          icon: BarChart3, 
          route: 'admin.reports', 
          component: 'Admin/Reports/Index',
          roles: ['admin', 'petugas']
        },
        { 
          label: 'Notifikasi & Alert', 
          icon: BadgeAlert, 
          route: 'admin.notifications', 
          component: 'Admin/Notifications/Index',
          roles: ['admin', 'petugas']
        }
      ]
    },
    {
      title: 'Konfigurasi Sistem',
      items: [
        { 
          label: 'Pengaturan Threshold Global', 
          icon: Settings, 
          route: 'admin.threshold-settings.index', 
          component: 'ThresholdSettings/Index',
          roles: ['admin']
        },
        { 
          label: 'Threshold per Lokasi', 
          icon: CloudRain, 
          route: 'admin.pump-house-thresholds.index', 
          component: 'PumpHouseThreshold/Index',
          roles: ['admin', 'petugas']
        },
        { 
          label: 'Akses Petugas', 
          icon: User, 
          route: 'admin.user-pump-house.index', 
          component: 'UserPumpHouse/Index',
          roles: ['admin']
        }
      ]
    },
    {
      title: 'Konten & Edukasi',
      items: [
        { 
          label: 'Manajemen Konten Edukasi', 
          icon: GraduationCap, 
          route: 'admin.education', 
          component: 'Education',
          roles: ['admin']
        }
      ]
    }
  ];

  // Filter sections and items based on user role
  return allSections.map(section => ({
    ...section,
    items: section.items.filter(item => item.roles.includes(userRole.value))
  })).filter(section => section.items.length > 0);
});

// State
const showNotifications = ref(false);
const notifications = ref([]);
const isLoadingNotifications = ref(false);
const unreadNotificationsCount = ref(0);

// Toggle functions
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    fetchNotifications();
  }
};

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  const notificationEl = document.querySelector('.notifications-dropdown');
  
  if (showNotifications.value && notificationEl && !notificationEl.contains(event.target) && !event.target.closest('button')?.classList.contains('notifications-trigger')) {
    showNotifications.value = false;
  }
};

// Fetch notifications from the database
const fetchNotifications = async () => {
  isLoadingNotifications.value = true;
  try {
    // Use web route as primary since we're using session auth
    const response = await axios.get('/admin/notifications/api');
    notifications.value = response.data.notifications || response.data;
    unreadNotificationsCount.value = response.data.unread_count || 0;
  } catch (error) {
    console.error('Error fetching notifications:', error);
    // Fallback to empty notifications if fails
    notifications.value = [];
    unreadNotificationsCount.value = 0;
  } finally {
    isLoadingNotifications.value = false;
  }
};

// View notification and mark as read
const viewNotification = async (notification) => {
  // Extract alert ID from notification ID for alert-based notifications
  let alertId = notification.id;
  if (typeof notification.id === 'string' && notification.id.startsWith('alert_')) {
    alertId = notification.id.replace('alert_', '');
  }
  
  // Mark as read and navigate
  if (!notification.read_at) {
    try {
      // Use web route as primary since we're using session auth
      await axios.post(`/admin/notifications/${alertId}/read`);
      notification.read_at = new Date().toISOString();
      unreadNotificationsCount.value = Math.max(0, unreadNotificationsCount.value - 1);
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }
  
  // Navigate to notification detail page using the correct route name
  router.visit(route('admin.notifications.show', alertId));
};

// Mark all notifications as read
const markAllAsRead = async () => {
  try {
    // Use web route as primary since we're using session auth
    await axios.post('/admin/notifications/read-all');
    notifications.value.forEach(notification => {
      notification.read_at = new Date().toISOString();
    });
    unreadNotificationsCount.value = 0;
  } catch (error) {
    console.error('Error marking all notifications as read:', error);
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  
  // Fetch notifications untuk admin dan petugas
  fetchUnreadCount();
  
  // Set up polling for notifications (every 30 seconds)
  const notificationInterval = setInterval(fetchUnreadCount, 30000);
  
  onBeforeUnmount(() => {
    clearInterval(notificationInterval);
  });
  
  document.addEventListener('visibilitychange', handleVisibilityChange);

  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
	document.removeEventListener('visibilitychange', handleVisibilityChange);
  });
});

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    fetchUnreadCount();
  }
};

// Fetch only the unread count (more efficient than fetching all notifications)
const fetchUnreadCount = async () => {
  try {
    // Use web route as primary since we're using session auth
    let response;
    try {
      response = await axios.get('/admin/notifications/count');
    } catch (error) {
      console.warn('Main route failed, trying test route:', error);
      // Fallback to test route
      response = await axios.get('/admin/notifications/test');
    }
    unreadNotificationsCount.value = response.data.unread_count || 0;
  } catch (error) {
    console.error('Error fetching notification count:', error);
    // Fallback to 0 if fails
    unreadNotificationsCount.value = 0;
  }
};

// Get severity label for display (consistent with threshold settings)
const getSeverityLabel = (severity) => {
  const labels = {
    'low': 'Normal',
    'medium': 'Peringatan', 
    'high': 'Kritis',
    'critical': 'Darurat'
  };
  return labels[severity] || 'Normal';
};
</script>