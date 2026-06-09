<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted } from 'vue'
import { Home, Car, Calendar, Info, LogIn, UserPlus, LogOut, User, LayoutDashboard, Menu, X, ChevronDown, Bell, MapPin, Clock, CheckCircle, XCircle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

interface Notification {
  id: string
  type: string
  message: string
  url: string | null
  created_at: string
}

const page = usePage()
const user = computed(() => page.props.auth?.user as any)
const isAdmin = computed(() => user.value?.role === 'ADMIN')
const notifications = computed(() => (page.props.notifications || []) as Notification[])
const flash = computed(() => page.props.flash as { success?: string; error?: string })

const mobileMenuOpen = ref(false)
const showFlash = ref(false)

// Flash message handling
watch(flash, (newFlash) => {
  if (newFlash?.success || newFlash?.error) {
    showFlash.value = true
    setTimeout(() => {
      showFlash.value = false
    }, 5000)
  }
}, { immediate: true })

// Detect if we're on admin pages
const currentPath = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.pathname
  }
  return '/'
})

const isAdminPage = computed(() => currentPath.value.startsWith('/admin'))

const navItems = [
  { href: '/', label: 'Beranda', icon: Home },
  { href: '/cars', label: 'Mobil', icon: Car },
  { href: '/packages', label: 'Paket Wisata', icon: MapPin },
  { href: '/dashboard', label: 'Dashboard', icon: Calendar, requireAuth: true },
  { href: '/about', label: 'Tentang', icon: Info },
]

const adminNavItems = [
  { href: '/admin', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/admin/validations', label: 'Verifikasi', icon: Clock },
  { href: '/admin/cars', label: 'Kelola Mobil', icon: Car },
  { href: '/admin/packages', label: 'Paket Wisata', icon: MapPin },
  { href: '/admin/bookings', label: 'Semua Booking', icon: Calendar },
]

const isActive = (href: string) => {
  if (href === '/' || href === '/admin') return currentPath.value === href
  return currentPath.value.startsWith(href)
}

const markNotificationsRead = () => {
  router.post('/notifications/mark-read', {}, { preserveScroll: true })
}

const handleNotificationClick = (notification: Notification) => {
  if (notification.url) {
    router.visit(notification.url)
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F7FA]">
    <!-- Flash Messages -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform -translate-y-full opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-full opacity-0"
    >
      <div v-if="showFlash && (flash?.success || flash?.error)" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4">
        <div
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg',
            flash?.success ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
          ]"
        >
          <CheckCircle v-if="flash?.success" class="w-5 h-5 flex-shrink-0" />
          <XCircle v-else class="w-5 h-5 flex-shrink-0" />
          <p class="flex-1 text-sm font-medium">{{ flash?.success || flash?.error }}</p>
          <button @click="showFlash = false" class="p-1 hover:bg-white/20 rounded">
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>
    </Transition>

    <!-- Admin Layout with Sidebar -->
    <template v-if="isAdminPage && isAdmin">
      <div class="flex">
        <!-- Admin Sidebar (Desktop) -->
        <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0 bg-[#060521] text-white">
          <!-- Logo -->
          <div class="flex items-center gap-x-3 px-6 py-5 border-b border-white/10">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#362EED] font-bold text-sm">
              MR
            </div>
            <div>
              <span class="font-poppins font-bold text-lg">MaaRentCar</span>
              <p class="text-xs text-gray-400">Admin Panel</p>
            </div>
          </div>

          <!-- Admin Navigation -->
          <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <Link
              v-for="item in adminNavItems"
              :key="item.href"
              :href="item.href"
              :class="[
                'flex items-center gap-x-3 px-4 py-3 rounded-lg transition-all font-medium',
                isActive(item.href)
                  ? 'bg-[#362EED] text-white'
                  : 'text-gray-300 hover:bg-white/10 hover:text-white'
              ]"
            >
              <component :is="item.icon" class="w-5 h-5" />
              {{ item.label }}
            </Link>

            <div class="pt-4 mt-4 border-t border-white/10">
              <p class="px-4 text-xs text-gray-500 uppercase tracking-wide mb-2">Lainnya</p>
              <Link
                href="/"
                class="flex items-center gap-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-white/10 hover:text-white transition-all font-medium"
              >
                <Home class="w-5 h-5" />
                Kembali ke Beranda
              </Link>
            </div>
          </nav>

          <!-- Admin User Section -->
          <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-x-3 px-4 py-2 mb-2">
              <div class="w-8 h-8 rounded-full bg-[#362EED] flex items-center justify-center">
                <User class="w-4 h-4" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ user?.name }}</p>
                <p class="text-xs text-gray-400">Administrator</p>
              </div>
            </div>
            <Link href="/logout" method="post" as="button" class="w-full">
              <Button variant="outline" class="w-full bg-transparent border-white/20 text-white hover:bg-white/10">
                <LogOut class="w-4 h-4 mr-2" />
                Keluar
              </Button>
            </Link>
          </div>
        </aside>

        <!-- Admin Mobile Header -->
        <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-[#060521] text-white">
          <div class="flex items-center justify-between px-4 py-3">
            <Link href="/admin" class="flex items-center gap-x-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#362EED] font-bold text-sm">
                MR
              </div>
              <div>
                <span class="font-poppins font-bold">MaaRentCar</span>
                <p class="text-xs text-gray-400">Admin</p>
              </div>
            </Link>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg hover:bg-white/10">
              <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
              <X v-else class="w-6 h-6" />
            </button>
          </div>

          <!-- Admin Mobile Menu -->
          <div v-if="mobileMenuOpen" class="border-t border-white/10 px-4 py-4 space-y-1">
            <Link
              v-for="item in adminNavItems"
              :key="item.href"
              :href="item.href"
              @click="mobileMenuOpen = false"
              :class="[
                'flex items-center gap-x-3 px-4 py-3 rounded-lg transition-all',
                isActive(item.href)
                  ? 'bg-[#362EED] text-white'
                  : 'text-gray-300 hover:bg-white/10'
              ]"
            >
              <component :is="item.icon" class="w-5 h-5" />
              {{ item.label }}
            </Link>
            <Link
              href="/"
              @click="mobileMenuOpen = false"
              class="flex items-center gap-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-white/10"
            >
              <Home class="w-5 h-5" />
              Kembali ke Beranda
            </Link>
            <div class="pt-3 border-t border-white/10">
              <Link href="/logout" method="post" as="button" class="w-full">
                <Button variant="outline" class="w-full bg-transparent border-white/20 text-white hover:bg-white/10">
                  <LogOut class="w-4 h-4 mr-2" />
                  Keluar
                </Button>
              </Link>
            </div>
          </div>
        </header>

        <!-- Admin Content -->
        <main class="flex-1 lg:pl-64 pt-16 lg:pt-0">
          <div class="min-h-screen bg-[#F5F7FA]">
            <slot />
          </div>
        </main>
      </div>
    </template>

    <!-- Customer Layout with Top Navbar -->
    <template v-else>
      <!-- Top Navbar -->
      <header class="sticky top-0 z-40 bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-x-2">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#362EED] text-white font-bold text-sm">
                MR
              </div>
              <span class="font-poppins font-bold text-lg text-[#060521]">MaaRentCar</span>
            </Link>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-x-1">
              <template v-for="item in navItems" :key="item.href">
                <Link
                  v-if="!item.requireAuth || user"
                  :href="item.href"
                  :class="[
                    'flex items-center gap-x-2 px-4 py-2 rounded-lg transition-all font-medium text-sm',
                    isActive(item.href)
                      ? 'bg-[#362EED] text-white'
                      : 'text-gray-600 hover:bg-gray-100 hover:text-[#060521]'
                  ]"
                >
                  <component :is="item.icon" class="w-4 h-4" />
                  {{ item.label }}
                </Link>
              </template>
            </nav>

            <!-- Desktop Auth/User Section -->
            <div class="hidden md:flex items-center gap-x-3">
              <!-- Admin Link -->
              <Link
                v-if="isAdmin"
                href="/admin"
                class="flex items-center gap-x-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"
              >
                <LayoutDashboard class="w-4 h-4" />
                Admin
              </Link>

              <!-- Notifications -->
              <DropdownMenu v-if="user">
                <DropdownMenuTrigger asChild>
                  <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <Bell class="w-5 h-5 text-gray-600" />
                    <span
                      v-if="notifications.length > 0"
                      class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                    />
                  </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-80">
                  <div class="flex items-center justify-between px-3 py-2 border-b">
                    <span class="font-semibold text-sm">Notifikasi</span>
                    <button
                      v-if="notifications.length > 0"
                      @click="markNotificationsRead"
                      class="text-xs text-[#362EED] hover:underline"
                    >
                      Tandai sudah dibaca
                    </button>
                  </div>
                  <div v-if="notifications.length > 0" class="max-h-64 overflow-y-auto">
                    <button
                      v-for="notif in notifications"
                      :key="notif.id"
                      @click="handleNotificationClick(notif)"
                      class="w-full px-3 py-2 text-left hover:bg-gray-50 border-b last:border-0"
                    >
                      <p class="text-sm">{{ notif.message }}</p>
                      <p class="text-xs text-gray-500 mt-1">{{ notif.created_at }}</p>
                    </button>
                  </div>
                  <div v-else class="px-3 py-6 text-center text-gray-500 text-sm">
                    Tidak ada notifikasi
                  </div>
                </DropdownMenuContent>
              </DropdownMenu>

              <template v-if="user">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <button class="flex items-center gap-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                      <div class="w-8 h-8 rounded-full bg-[#362EED] flex items-center justify-center text-white">
                        <User class="w-4 h-4" />
                      </div>
                      <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
                      <ChevronDown class="w-4 h-4 text-gray-400" />
                    </button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-48">
                    <div class="px-3 py-2">
                      <p class="text-sm font-medium">{{ user.name }}</p>
                      <p class="text-xs text-gray-500">{{ user.email }}</p>
                    </div>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                      <Link href="/dashboard" class="flex items-center gap-x-2 cursor-pointer">
                        <Calendar class="w-4 h-4" />
                        Dashboard
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                      <Link href="/logout" method="post" as="button" class="flex items-center gap-x-2 w-full text-red-600 cursor-pointer">
                        <LogOut class="w-4 h-4" />
                        Keluar
                      </Link>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </template>
              <template v-else>
                <Link href="/login">
                  <Button variant="outline" size="sm">
                    <LogIn class="w-4 h-4 mr-2" />
                    Masuk
                  </Button>
                </Link>
                <Link href="/register">
                  <Button size="sm" class="bg-[#362EED] hover:bg-[#2a24c4]">
                    <UserPlus class="w-4 h-4 mr-2" />
                    Daftar
                  </Button>
                </Link>
              </template>
            </div>

            <!-- Mobile Menu Button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-2 rounded-lg hover:bg-gray-100"
            >
              <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
              <X v-else class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white px-4 py-4 space-y-1">
          <template v-for="item in navItems" :key="item.href">
            <Link
              v-if="!item.requireAuth || user"
              :href="item.href"
              @click="mobileMenuOpen = false"
              :class="[
                'flex items-center gap-x-3 px-4 py-3 rounded-lg transition-all',
                isActive(item.href)
                  ? 'bg-[#362EED] text-white'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <component :is="item.icon" class="w-5 h-5" />
              {{ item.label }}
            </Link>
          </template>

          <Link
            v-if="isAdmin"
            href="/admin"
            @click="mobileMenuOpen = false"
            class="flex items-center gap-x-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100"
          >
            <LayoutDashboard class="w-5 h-5" />
            Admin Dashboard
          </Link>

          <div class="pt-3 border-t space-y-2">
            <template v-if="user">
              <div class="flex items-center gap-x-3 px-4 py-2">
                <div class="w-8 h-8 rounded-full bg-[#362EED] flex items-center justify-center text-white">
                  <User class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm font-medium">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
              </div>
              <Link href="/logout" method="post" as="button" class="w-full" @click="mobileMenuOpen = false">
                <Button variant="outline" class="w-full">
                  <LogOut class="w-4 h-4 mr-2" />
                  Keluar
                </Button>
              </Link>
            </template>
            <template v-else>
              <Link href="/login" class="block" @click="mobileMenuOpen = false">
                <Button variant="outline" class="w-full">
                  <LogIn class="w-4 h-4 mr-2" />
                  Masuk
                </Button>
              </Link>
              <Link href="/register" class="block" @click="mobileMenuOpen = false">
                <Button class="w-full bg-[#362EED] hover:bg-[#2a24c4]">
                  <UserPlus class="w-4 h-4 mr-2" />
                  Daftar
                </Button>
              </Link>
            </template>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="bg-white min-h-[calc(100vh-4rem)]">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="bg-[#060521] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
          <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-x-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#362EED] font-bold text-xs">
                MR
              </div>
              <span class="font-poppins font-bold">MaaRentCar</span>
            </div>
            <p class="text-sm text-gray-400">&copy; 2024 MaaRentCar. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </template>
  </div>
</template>
