@extends('layouts.app')

@section('title', 'Client Member Dashboard')
@section('page-title', 'Client Member Dashboard')

@section('content')
  <div class="space-y-6">
    <!-- Generate Short URL Form -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Short URL</h3>
      <form id="generateUrlForm" class="space-y-4">
        @csrf
        <div>
          <label for="long_url" class="block text-sm font-medium text-gray-700 mb-1">
            Long URL
          </label>
          <input type="url" id="long_url" name="long_url" required
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            placeholder="e.g. https://sembark.com/travel-software/features/best-itinerary-builder">
        </div>
        <button type="submit"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Generate
        </button>
      </form>

      <!-- Generated URL Display -->
      <div id="generatedUrl" class="mt-4 p-4 bg-green-50 rounded-md hidden">
        <p class="text-sm text-green-800">Short URL generated successfully!</p>
        <p class="mt-1 font-medium">
          <span id="shortUrlDisplay" class="text-blue-600"></span>
          <button onclick="copyToClipboard()" class="ml-2 text-sm text-gray-500 hover:text-gray-700">
            <i class="far fa-copy"></i> Copy
          </button>
        </p>
      </div>
    </div>

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

    <!-- URL List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Generated Short URLs</h3>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Long URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created On</th>
              {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              --}}
            </tr>
          </thead>
          <tbody id="urlTableBody" class="bg-white divide-y divide-gray-200">
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
                  <div class="max-w-xs truncate" title="{{ $url->long_url }}">
                    {{ $url->long_url }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ $url->hits }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $url->created_at->format('d M Y') }}
                </td>
                {{-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button onclick="deleteUrl({{ $url->id }})" class="text-red-600 hover:text-red-900">
                    Delete
                  </button>
                </td> --}}
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($shortUrls->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ $shortUrls->firstItem() }} to {{ $shortUrls->lastItem() }} of {{ $shortUrls->total() }} results
            </div>
            <div class="flex space-x-2">
              @if($shortUrls->onFirstPage())
                <span class="px-3 py-1 text-gray-400">← Prev</span>
              @else
                <a href="{{ $shortUrls->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
              @endif

              @if($shortUrls->hasMorePages())
                <a href="{{ $shortUrls->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
              @else
                <span class="px-3 py-1 text-gray-400">Next →</span>
              @endif
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  @push('scripts')
    <script>
      $(document).ready(function () {
        // Generate URL Form Submission
        $('#generateUrlForm').submit(function (e) {
          e.preventDefault();

          $.ajax({
            url: '{{ route("short-urls.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
              if (response.success) {
                $('#shortUrlDisplay').text(response.short_url);
                $('#generatedUrl').removeClass('hidden');

                // Add to table
                const newRow = `
                                          <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                              <div class="flex items-center">
                                                <a href="${response.short_url}" target="_blank" class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium">
                                                  ${response.short_code}
                                                </a>
                                                <button onclick="copySingleUrl('${response.short_url}')" class="ml-2 text-gray-400 hover:text-gray-600">
                                                  <i class="far fa-copy text-xs"></i>
                                                </button>
                                              </div>
                                            </td>
                                            <td class="px-6 py-4 max-w-xs truncate" title="${$('#long_url').val()}">
                                              ${$('#long_url').val()}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                0
                                              </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                              Just now
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                              <button onclick="deleteUrl('temp')" class="text-red-600 hover:text-red-900">
                                                Delete
                                              </button>
                                            </td>
                                          </tr>
                                        `;

                $('#urlTableBody').prepend(newRow);
                $('#long_url').val('');
              }
            },
            error: function (xhr) {
              alert('Error generating URL: ' + xhr.responseJSON.error);
            }
          });
        });
      });

      // Date Filter Change
      document.getElementById('dateFilter').addEventListener('change', function () {
        const filterValue = this.value;

        if (filterValue === 'all') {
          // Remove filter parameter if "All Time" is selected
          window.location.href = removeQueryParam(window.location.href, 'filter');
        } else {
          window.location.href = updateQueryStringParameter(window.location.href, 'filter', filterValue);
        }
      });

      // Download CSV
      document.getElementById('downloadBtn').addEventListener('click', function () {
        const filter = document.getElementById('dateFilter').value;

        let downloadUrl = "{{ route('short-urls.export-member') }}";
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

      function copyToClipboard() {
        const text = $('#shortUrlDisplay').text();
        navigator.clipboard.writeText(text).then(function () {
          alert('URL copied to clipboard!');
        });
      }

      function copySingleUrl(url) {
        navigator.clipboard.writeText(url).then(function () {
          alert('URL copied to clipboard!');
        }).catch(function (err) {
          console.error('Could not copy text: ', err);
          alert('Failed to copy URL. Please try again.');
        });
      }

      function deleteUrl(id) {
        if (!confirm('Are you sure you want to delete this URL?')) return;

        $.ajax({
          url: '/short-urls/' + id,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function (response) {
            if (response.success) {
              location.reload();
            }
          },
          error: function (xhr) {
            alert('Error deleting URL: ' + xhr.responseJSON.error);
          }
        });
      }
    </script>
  @endpush
@endsection