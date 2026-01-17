{{-- resources/views/client-admin/short-urls/index.blade.php --}}
@extends('layouts.app')

@section('title', 'All Generated Short URLs')
@section('page-title', 'Generated Short URLs')

@section('content')
  <div class="space-y-6">
    <!-- Filter and Download Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
          <!-- Filter Dropdown -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Date</label>
            <select id="dateFilter"
              class="block w-full md:w-48 px-4 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All Time</option>
              <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
              <option value="last_week" {{ request('filter') == 'last_week' ? 'selected' : '' }}>Last Week</option>
              <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
              <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
              {{-- <option value="custom" {{ request('filter')=='custom' ? 'selected' : '' }}>Custom Range</option> --}}
            </select>
          </div>
        </div>

        <!-- Download Button -->
        <div>
          <button id="downloadBtn"
            class="flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-download mr-2"></i>
            Download CSV
          </button>
        </div>
      </div>
    </div>

    <!-- URLs Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">All Generated Short URLs</h3>
          <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">
            ← Back to Dashboard
          </a>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SHORT URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LONG URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HITS</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CREATED BY</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CREATED ON</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($shortUrls as $url)
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <a href="{{ $url->short_url }}" target="_blank"
                      class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium">
                      {{ $url->short_code }}
                    </a>
                    <a href="{{ $url->short_url }}" class="ml-2">
                      <button type="button" class="text-gray-400 hover:text-gray-600">
                        <i class="far fa-copy text-xs"></i>
                      </button>
                    </a>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="max-w-md truncate" title="{{ $url->long_url }}">
                    <span class="text-gray-700 text-sm">{{ $url->long_url }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ $url->hits }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ $url->user->name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ $url->created_at->format('d M Y') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $shortUrls->firstItem() }} to {{ $shortUrls->lastItem() }} of {{ $shortUrls->total() }} results
          </div>
          <div class="flex space-x-4">
            <!-- Previous Page Link -->
            @if($shortUrls->onFirstPage())
              <span class="px-3 py-1 text-gray-400 cursor-not-allowed">← Prev</span>
            @else
              <a href="{{ $shortUrls->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
            @endif

            <!-- Next Page Link -->
            @if($shortUrls->hasMorePages())
              <a href="{{ $shortUrls->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
            @else
              <span class="px-3 py-1 text-gray-400 cursor-not-allowed">Next →</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      // Date Filter Change
      document.getElementById('dateFilter').addEventListener('change', function () {
        const filterValue = this.value;
        if (filterValue !== 'all') {
          window.location.href = updateQueryStringParameter(window.location.href, 'filter', filterValue);
        } else {
          // Remove filter parameter if "All Time" is selected
          window.location.href = removeQueryParam(window.location.href, 'filter');
        }
      });

      // Download CSV
      document.getElementById('downloadBtn').addEventListener('click', function () {
        const filter = document.getElementById('dateFilter').value;
        let downloadUrl = "{{ route('short-urls.export') }}";
        downloadUrl += `?filter=${filter}`;

        window.location.href = downloadUrl;
      });

      // Helper function to update query string
      function updateQueryStringParameter(uri, key, value) {
        const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        const separator = uri.indexOf('?') !== -1 ? "&" : "?";

        if (uri.match(re)) {
          return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
          return uri + separator + key + "=" + value;
        }
      }

      // Helper function to remove query parameter
      function removeQueryParam(url, parameter) {
        const urlParts = url.split('?');
        if (urlParts.length >= 2) {
          const prefix = encodeURIComponent(parameter) + '=';
          const pars = urlParts[1].split(/[&;]/g);

          for (let i = pars.length; i-- > 0;) {
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
              pars.splice(i, 1);
            }
          }

          url = urlParts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
          return url;
        } else {
          return url;
        }
      }

      // Copy URL function
      function copySingleUrl(url) {
        navigator.clipboard.writeText(url).then(function () {
          alert('URL copied to clipboard!');
        }).catch(function (err) {
          console.error('Could not copy text: ', err);
          alert('Failed to copy URL. Please try again.');
        });
      }
    </script>
  @endpush
@endsection