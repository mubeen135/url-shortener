@extends('layouts.app')

@section('title', 'Super Admin - Generated Short URLs')
@section('page-title', 'Generated Short URLs')

@section('content')
  <div class="space-y-6">
    <!-- Stats Section -->
    {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-link text-2xl text-blue-500"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total URLs</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalUrls }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-chart-line text-2xl text-green-500"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Hits</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalHits }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-building text-2xl text-purple-500"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Companies</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalCompanies }}</p>
          </div>
        </div>
      </div>
    </div> --}}

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center space-x-4 mb-3 md:mb-0">
          <div class="relative">
            <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Date</label>
            <select id="filter" name="filter" onchange="applyFilter(this.value)"
              class="block w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Time</option>
              <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
              <option value="last_week" {{ request('filter') == 'last_week' ? 'selected' : '' }}>Last Week</option>
              <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
              <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mt-6">
              <i class="fas fa-chevron-down text-gray-400"></i>
            </div>
          </div>
          
          @if(request('filter'))
            <a href="{{ route('short-urls.index') }}"
              class="px-4 py-2 mt-6 text-sm font-medium bg-gray-100 text-gray-700 rounded-md border border-gray-300 hover:bg-gray-200">
              Clear Filter
            </a>
          @endif
        </div>
        
        <div class="flex items-center">
          <button onclick="exportToCSV()"
            class="px-4 py-2 text-sm font-medium bg-green-100 text-green-700 rounded-md border border-green-300 hover:bg-green-200 flex items-center">
            <i class="fas fa-download mr-2"></i> Download
          </button>
        </div>
      </div>
    </div>

    <!-- Short URLs Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">All Generated Short URLs</h3>
          <div class="text-sm text-gray-600">
            @if(request('filter'))
              Showing URLs from 
              @switch(request('filter'))
                @case('today')
                  Today
                  @break
                @case('last_week')
                  Last Week
                  @break
                @case('last_month')
                  Last Month
                  @break
                @case('this_month')
                  This Month
                  @break
                @default
                  All Time
              @endswitch
            @else
              Showing All URLs
            @endif
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Long URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created On</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($shortUrls as $url)
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
                        <div class="max-w-xs truncate" title="{{ $url->long_url }}">
                          {{ $url->long_url }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                        {{ $url->hits > 100 ? 'bg-green-100 text-green-800' :
              ($url->hits > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                          {{ $url->hits }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $url->company->name }}</div>
                        <div class="text-xs text-gray-500">{{ $url->company->email }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $url->user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $url->user->email }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $url->created_at->format('d M Y') }}<br>
                        <span class="text-xs text-gray-400">{{ $url->created_at->format('h:i A') }}</span>
                      </td>
                    </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                  <i class="fas fa-link text-3xl text-gray-300 mb-2"></i>
                  <p class="text-lg">No short URLs found</p>
                  @if(request('filter'))
                    <p class="text-sm mt-1">No URLs found for the selected filter</p>
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination Section -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $shortUrls->firstItem() }} to {{ $shortUrls->lastItem() }} of {{ $shortUrls->total() }} results
          </div>
          <div class="flex space-x-2">
            <!-- Previous Page Link -->
            @if($shortUrls->onFirstPage())
              <span class="px-3 py-1 text-gray-400 cursor-not-allowed">← Prev</span>
            @else
              <a href="{{ $shortUrls->previousPageUrl() }}{{ request('filter') ? '&filter=' . request('filter') : '' }}"
                class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
            @endif

            <!-- Next Page Link -->
            @if($shortUrls->hasMorePages())
              <a href="{{ $shortUrls->nextPageUrl() }}{{ request('filter') ? '&filter=' . request('filter') : '' }}"
                class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
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
      function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
          alert('URL copied to clipboard!');
        }).catch(function (err) {
          console.error('Could not copy text: ', err);
        });
      }

      function applyFilter(filterValue) {
        const url = new URL(window.location.href);
        
        if (filterValue) {
          url.searchParams.set('filter', filterValue);
        } else {
          url.searchParams.delete('filter');
        }
        
        window.location.href = url.toString();
      }

      function exportToCSV() {
        // Get table data
        let csv = [];
        let rows = document.querySelectorAll("table tr");

        for (let i = 0; i < rows.length; i++) {
          let row = [], cols = rows[i].querySelectorAll("td, th");

          for (let j = 0; j < cols.length; j++) {
            // Get text content, remove extra whitespace
            let text = cols[j].textContent.trim();
            // Escape quotes and wrap in quotes if contains comma
            text = text.replace(/"/g, '""');
            if (text.includes(",") || text.includes("\"") || text.includes("\n")) {
              text = '"' + text + '"';
            }
            row.push(text);
          }
          csv.push(row.join(","));
        }

        // Download CSV file
        let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "short_urls_" + new Date().toISOString().split('T')[0] + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    </script>
  @endpush
@endsection