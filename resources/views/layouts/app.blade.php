<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'URL Shortener')</title>

  <!-- Use Laravel's asset() helper for local files -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <!-- CDN links -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Inline styles as backup -->
  <style>
    .sidebar {
      transition: all 0.3s ease;
    }

    .active-menu {
      background-color: #3b82f6;
      color: white;
    }

    .hidden {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-50">
  @if(auth()->check())
    <div class="flex h-screen">
      <!-- Sidebar -->

      <div class="sidebar bg-white w-64 border-r border-gray-200">
        <div class="p-4">
          <h1 class="text-xl font-bold text-blue-600">
            <i class="fas fa-link mr-2"></i>URL Shortener
          </h1>
          <div class="mt-6">
            @if(auth()->user()->isSuperAdmin())
              <!-- Super Admin Menu -->
              <a href="{{ route('dashboard') }}"
                class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Super Admin Panel
              </a>
              <a href="{{ route('clients') }}"
                class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('clients') ? 'active-menu' : '' }}">
                <i class="fas fa-building mr-2"></i>Clients
              </a>
              <a href="{{ route('short-urls.index') }}"
                class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('short-urls.index') ? 'active-menu' : '' }}">
                <i class="fas fa-link mr-2"></i>Generated Short URLs
              </a>
            @else
              <!-- Company Admin/Member Menu -->
              <a href="{{ route('dashboard') }}"
                class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
              </a>

              <!-- Generated Short URLs Tab -->
              <a href="{{ route('short-urls.data') }}"
                class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('short-urls.data') ? 'active-menu' : '' }}">
                <i class="fas fa-link mr-2"></i>Generated Short URLs
              </a>

              <!-- Team Members Tab (Only for Company Admins) -->
              @if(auth()->user()->role === 'admin')
                <a href="{{ route('team.index') }}"
                  class="block py-2 px-4 rounded-lg hover:bg-blue-50 mb-2 {{ request()->routeIs('team.index') ? 'active-menu' : '' }}">
                  <i class="fas fa-users mr-2"></i>Team Members
                </a>
              @endif
            @endif
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <!-- Top Navigation -->
        <nav class="bg-white border-b border-gray-200 px-6 py-4">
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">
                @yield('page-title', 'Dashboard')
              </h2>
            </div>
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-600">
                {{ auth()->user()->name }}
                <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                            {{ auth()->user()->isSuperAdmin() ? 'bg-purple-100 text-purple-800' :
      (auth()->user()->isAdmin() ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                  {{ ucfirst(auth()->user()->role) }}
                </span>
              </span>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-gray-800 flex items-center">
                  <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </button>
              </form>
            </div>
          </div>
        </nav>

        <!-- Page Content -->
        <main class="p-6">
          @yield('content')
        </main>
      </div>
    </div>
  @else
    @yield('auth-content')
  @endif

  <!-- Load jQuery from CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- CSRF Token for AJAX requests -->
  <script>
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    });
  </script>

  @stack('scripts')
</body>

</html>