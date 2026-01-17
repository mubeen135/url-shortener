@extends('layouts.app')

@section('title', 'Super Admin Panel')
@section('page-title', 'Super Admin Dashboard')

@section('content')
  <div class="space-y-6">
    <!-- Clients Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Clients</h3>
          <button onclick="showClientInviteForm()"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
            + Invite New Client
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Generated
                URLs</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total URL Hits
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($companies as $company)
              <tr>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ $company->name }}</div>
                  <div class="text-sm text-gray-500">{{ $company->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $company->users_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $company->short_urls_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $company->short_urls_sum_hits }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination exactly like in image -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $companies->count() }} of total {{ $companies->total() }}
          </div>
          <div class="flex items-center space-x-4">
            <!-- Pagination buttons -->
            <div class="flex space-x-2">
              @if($companies->onFirstPage())
                <span class="px-3 py-1 text-gray-400 cursor-not-allowed">← Prev</span>
              @else
                <a href="{{ $companies->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
              @endif

              @if($companies->hasMorePages())
                <a href="{{ $companies->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
              @else
                <span class="px-3 py-1 text-gray-400 cursor-not-allowed">Next →</span>
              @endif
            </div>

            <!-- View All button -->
            <a href="{{ route('clients') }}?all=true" class="text-sm text-blue-600 hover:text-blue-800">[View All]</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Generated Short URLs Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Generated Short URLs</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Long URL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created On</th>
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
                  {{ $url->company->name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $url->created_at->format('d M Y') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination exactly like in image -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $shortUrls->count() }} of total {{ @$shortUrls->total() }}
          </div>
          <div class="flex items-center space-x-4">
            <!-- Pagination buttons -->
            <div class="flex space-x-2">
              @if($shortUrls->onFirstPage())
                <span class="px-3 py-1 text-gray-400 cursor-not-allowed">← Prev</span>
              @else
                <a href="{{ $shortUrls->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
              @endif

              @if($shortUrls->hasMorePages())
                <a href="{{ $shortUrls->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
              @else
                <span class="px-3 py-1 text-gray-400 cursor-not-allowed">Next →</span>
              @endif
            </div>

            <!-- View All button -->
            <a href="{{ route('short-urls.index') }}" class="text-sm text-blue-600 hover:text-blue-800">[View
              All]</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Invite Client Modal -->
  <div id="clientInviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Invite New Client</h3>

        <form id="clientInviteForm" class="space-y-4">
          @csrf
          <div>
            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">
              Name
            </label>
            <input type="text" id="client_name" name="name" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Client Name...">
          </div>

          <div>
            <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input type="email" id="client_email" name="email" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="ex. sample@example.com">
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="hideClientInviteForm()"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
              Send Invitation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function showClientInviteForm() {
        $('#clientInviteModal').removeClass('hidden');
      }

      function hideClientInviteForm() {
        $('#clientInviteModal').addClass('hidden');
      }

      $(document).ready(function () {
        // Client Invite Form
        $('#clientInviteForm').submit(function (e) {
          e.preventDefault();

          $.ajax({
            url: '{{ route("clients.invite") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
              if (response.success) {
                alert('Client invitation sent successfully!');
                hideClientInviteForm();
                $('#clientInviteForm')[0].reset();
                location.reload();
              }
            },
            error: function (xhr) {
              alert('Error sending invitation: ' + xhr.responseJSON.error);
            }
          });
        });
      });

      // Close modal when clicking outside
      window.onclick = function (event) {
        const modal = document.getElementById('clientInviteModal');
        if (event.target == modal) {
          hideClientInviteForm();
        }
      }
    </script>
  @endpush
@endsection