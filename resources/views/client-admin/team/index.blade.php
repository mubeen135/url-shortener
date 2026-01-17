{{-- resources/views/client-admin/team/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Team Members')
@section('page-title', 'Team Members')

@section('content')
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Team Members</h3>
          </div>
          <div class="flex space-x-2">
            <a href="{{ route('dashboard') }}"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
              ← Back to Dashboard
            </a>
            <button onclick="showInviteForm()"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
              + Invite New Member
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Members Table -->
    <div class="bg-white rounded-lg shadow">

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EMAIL</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROLE</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URLS</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HITS</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JOINED</th>
              {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTIONS</th>
              --}}
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($teamMembers as $member)
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $member->email }}
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $member->created_at->format('d M Y') }}
                </td>
                {{-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button onclick="editMember({{ $member->id }})"
                    class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                  @if($member->id != auth()->id())
                  <button onclick="deleteMember({{ $member->id }})" class="text-red-600 hover:text-red-900">Remove</button>
                  @endif
                </td> --}}
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $teamMembers->firstItem() }} to {{ $teamMembers->lastItem() }} of {{ $teamMembers->total() }}
            results
          </div>
          <div class="flex space-x-4">
            <!-- Previous Page Link -->
            @if($teamMembers->onFirstPage())
              <span class="px-3 py-1 text-gray-400 cursor-not-allowed">← Prev</span>
            @else
              <a href="{{ $teamMembers->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">← Prev</a>
            @endif

            <!-- Next Page Link -->
            @if($teamMembers->hasMorePages())
              <a href="{{ $teamMembers->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">Next →</a>
            @else
              <span class="px-3 py-1 text-gray-400 cursor-not-allowed">Next →</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Invite Modal (Same as dashboard) -->
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

      $(document).ready(function () {
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
      });

      // Close modal when clicking outside
      window.onclick = function (event) {
        const modal = document.getElementById('inviteModal');
        if (event.target == modal) {
          hideInviteForm();
        }
      }
    </script>
  @endpush
@endsection