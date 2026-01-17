@extends('layouts.app')

@section('title', 'Client Admin Dashboard')
@section('page-title', 'Client Admin Dashboard')

@section('content')
  <div class="space-y-8">
    <!-- Generate Short URL Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Generate Short URL</h3>
      </div>
      <div class="px-6 py-6">
        <form id="generateUrlForm" class="space-y-4">
          @csrf
          <div>
            <label for="long_url" class="block text-sm font-medium text-gray-700 mb-2">
              <strong>Long URL</strong>
            </label>
            <div class="flex space-x-4">
              <input type="url" id="long_url" name="long_url" required
                class="flex-1 px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g. https://sembark.com/travel-software/features/best-itinerary-builder">
              <button type="submit"
                class="px-8 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Generate
              </button>
            </div>
          </div>
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
    </div>

    <!-- Horizontal Line Separator -->
    <div class="relative">
      <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-300"></div>
      </div>
    </div>

    <!-- Generated Short URLs Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Generated Short URLs</h3>
          <span class="text-sm text-gray-600">Showing {{ $shortUrls->count() }} of total {{ $totalGenerated }}</span>
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
                    <button onclick="copySingleUrl('{{ $url->short_url }}')" class="ml-2 text-gray-400 hover:text-gray-600">
                      <i class="far fa-copy text-xs"></i>
                    </button>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="max-w-xs truncate" title="{{ $url->long_url }}">
                    <span class="text-gray-700 text-sm">{{ Str::limit($url->long_url, 40) }}</span>
                    @if(strlen($url->long_url) > 40)
                      <span class="text-gray-400">...</span>
                    @endif
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
          <a href="{{ route('short-urls.data') }}" class="text-blue-600 hover:text-blue-800">
            [View All]
          </a>
        </div>
      </div>
    </div>

    <!-- Horizontal Line Separator -->
    <div class="relative">
      <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-300"></div>
      </div>
    </div>

    <!-- Team Members Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-800">Team Members</h3>
          <button onclick="showInviteForm()" class="text-sm text-blue-600 hover:text-blue-800">
            + Invite
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROLE</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URLS</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HITS</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($teamMembers as $member)
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                  <div class="text-xs text-gray-500">{{ $member->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                        {{ $member->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                    {{ ucfirst($member->role) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $member->short_urls_count ?? 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $member->short_urls_sum_hits ?? 0 }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex justify-end">
          <a href="{{ route('team.index') }}" class="text-blue-600 hover:text-blue-800">
            [View All]
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Invite Modal -->
  <div id="inviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Invite New Team Member</h3>

        <form id="inviteForm" class="space-y-4">
          @csrf
          <div>
            <label for="invite_name" class="block text-sm font-medium text-gray-700 mb-1">
              Name
            </label>
            <input type="text" id="invite_name" name="name" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="User Name">
          </div>

          <div>
            <label for="invite_email" class="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input type="email" id="invite_email" name="email" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="ex. sample@example.com">
          </div>

          <div>
            <label for="invite_role" class="block text-sm font-medium text-gray-700 mb-1">
              Role
            </label>
            <select id="invite_role" name="role" required
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option value="member">Member</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="hideInviteForm()"
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
      function showInviteForm() {
        $('#inviteModal').removeClass('hidden');
      }

      function hideInviteForm() {
        $('#inviteModal').addClass('hidden');
        $('#inviteForm')[0].reset();
      }

      function copySingleUrl(url) {
        navigator.clipboard.writeText(url).then(function () {
          alert('URL copied to clipboard!');
        }).catch(function (err) {
          console.error('Could not copy text: ', err);
          alert('Failed to copy URL. Please try again.');
        });
      }

      function copyToClipboard() {
        const text = $('#shortUrlDisplay').text();
        navigator.clipboard.writeText(text).then(function () {
          alert('URL copied to clipboard!');
        }).catch(function (err) {
          console.error('Could not copy text: ', err);
          alert('Failed to copy URL. Please try again.');
        });
      }

      $(document).ready(function () {
        // Generate URL Form
        $('#generateUrlForm').submit(function (e) {
          e.preventDefault();

          // Show loading state
          const generateBtn = $(this).find('button[type="submit"]');
          const originalText = generateBtn.html();
          generateBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Generating...');
          generateBtn.prop('disabled', true);

          $.ajax({
            url: '{{ route("short-urls.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              if (response.success) {
                // Show generated URL
                $('#shortUrlDisplay').text(response.short_url);
                $('#generatedUrl').removeClass('hidden');
                $('#long_url').val('');

                // Auto-copy to clipboard
                navigator.clipboard.writeText(response.short_url).then(function () {
                  console.log('Auto-copied to clipboard');
                });

                // Reload the page after 1.5 seconds to show the new URL in the list
                setTimeout(function () {
                  window.location.reload();
                }, 1500);
              }
            },
            error: function (xhr) {
              let errorMessage = 'Error generating URL';
              if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
              } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              alert(errorMessage);
            },
            complete: function () {
              // Restore button state
              generateBtn.html(originalText);
              generateBtn.prop('disabled', false);
            }
          });
        });

        // Invite Form
        $('#inviteForm').submit(function (e) {
          e.preventDefault();

          $.ajax({
            url: '{{ route("invite.team") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              if (response.success) {
                alert('Invitation sent successfully!');
                hideInviteForm();
                location.reload();
              }
            },
            error: function (xhr) {
              let errorMessage = 'Error sending invitation';
              if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
              } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              alert(errorMessage);
            }
          });
        });

        // Auto-focus on URL input
        $('#long_url').focus();

        // Handle Enter key in form
        $('#long_url').keypress(function (e) {
          if (e.which === 13) {
            e.preventDefault();
            $('#generateUrlForm').submit();
          }
        });
      });

      // Close generated URL notification after 5 seconds
      setTimeout(function () {
        $('#generatedUrl').addClass('hidden');
      }, 5000);

      // Close modal when clicking outside
      window.onclick = function (event) {
        const modal = document.getElementById('inviteModal');
        if (event.target == modal) {
          hideInviteForm();
        }
      }
    </script>
  @endpush

  <style>
    /* Custom styles to match the image exactly */
    table {
      border-collapse: separate;
      border-spacing: 0;
    }

    thead th {
      font-weight: 500;
      letter-spacing: 0.05em;
      color: #6b7280;
    }

    tbody tr {
      transition: background-color 0.15s ease-in-out;
    }

    tbody tr:hover {
      background-color: #f9fafb;
    }

    .truncate {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Style for the separator lines */
    .border-t {
      border-color: #d1d5db;
    }

    /* Button hover effects */
    button:hover {
      transition: all 0.2s ease-in-out;
    }

    /* Form input focus styles */
    input:focus,
    select:focus {
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Make the "Long URL" label bold */
    label strong {
      font-weight: 600;
    }

    /* Space between sections */
    .space-y-8>*+* {
      margin-top: 2rem;
    }

    /* Invite button styling */
    button.text-blue-600:hover {
      text-decoration: underline;
    }
  </style>
@endsection