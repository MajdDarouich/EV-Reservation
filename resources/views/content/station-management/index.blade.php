@extends('layouts/contentNavbarLayout')

@section('title', 'Stations')

@section('content')
  @php
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  @endphp
  <div class="row">
    <div class="col-md-12">

      {{-- Flash messages --}}
      @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-4">
          <div>
            <h5 class="mb-0">Stations</h5>
            <small class="text-body">Manage charging stations and their status</small>
          </div>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStationModal">
            <i class="icon-base bx bx-map-pin me-1"></i> Add New Station
          </button>
        </div>

        <div class="card-body border-bottom">
          <form method="GET" action="{{ route('stations.index') }}" class="row g-4 align-items-center">
            <div class="col-md-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text" id="stationSearchAddon"><i class="icon-base bx bx-map-pin"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by name or location..."
                  aria-label="Search..." aria-describedby="stationSearchAddon" value="{{ request('search') }}" />
              </div>
            </div>
            <div class="col-md-auto">
              <button type="submit" class="btn btn-primary">Search</button>
              @if (request('search'))
                <a href="{{ route('stations.index') }}" class="btn btn-outline-secondary">Clear</a>
              @endif
            </div>
          </form>
        </div>

        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
              <tr>
                <th>Station Name</th>
                <th>Address</th>
                <th>City / Country</th>
                <th>Contact</th>
                <th>Coordinates</th>
                <th>Facilities</th>
                <th>Working Hours</th>
                <th>Status</th>
                <th>Rating</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse ($stations as $station)
                @php
                  $statusColors = [
                      'Active' => 'success',
                      'Inactive' => 'secondary',
                      'Maintenance' => 'warning',
                  ];
                  $status = $station->status->value;
                  $statusColor = $statusColors[$status] ?? 'secondary';
                @endphp
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-3">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                          <i class="icon-base bx bx-map-pin"></i>
                        </span>
                      </div>
                      <span class="fw-medium">{{ $station->name }}</span>
                    </div>
                  </td>
                  <td>{{ $station->address }}</td>
                  <td>{{ $station->city }}, {{ $station->country }}</td>
                  <td>{{ $station->contact_info }}</td>
                  <td>{{ $station->latitude }}, {{ $station->longitude }}</td>
                  <td>{{ $station->facilities ?: 'None listed' }}</td>
                  <td>
                    @forelse ($station->stationWorkingHours->sortBy('day_of_week') as $workingHour)
                      <div>
                        <strong>{{ $dayNames[$workingHour->day_of_week] ?? 'Unknown day' }}:</strong>
                        @if ($workingHour->is_opened)
                          {{ \Illuminate\Support\Carbon::parse($workingHour->opens_at)->format('g:i A') }} -
                          {{ \Illuminate\Support\Carbon::parse($workingHour->closes_at)->format('g:i A') }}
                        @else
                          Closed
                        @endif
                      </div>
                    @empty
                      <span class="text-body-secondary">Not configured</span>
                    @endforelse
                  </td>
                  <td>
                    <span class="badge bg-label-{{ $statusColor }}">
                      {{ $status }}
                    </span>
                  </td>
                  <td>{{ number_format((float) $station->avg_rating, 2) }} / 5</td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                          data-bs-target="#editStationModal{{ $station->id }}">
                          <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                          onclick="document.getElementById('deleteStationForm{{ $station->id }}').submit();">
                          <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>
                      </div>
                    </div>

                    {{-- Hidden delete form --}}
                    <form id="deleteStationForm{{ $station->id }}"
                      action="{{ route('stations.destroy', $station->id) }}" method="POST" class="d-none"
                      onsubmit="return confirm('Delete {{ $station->name }}? This cannot be undone.');">
                      @csrf
                      @method('DELETE')
                    </form>
                  </td>
                </tr>

                {{-- Edit Station Modal --}}
                <div class="modal fade" id="editStationModal{{ $station->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('stations.update', $station->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Station</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-6">
                            <label for="stationName{{ $station->id }}" class="form-label">Station Name</label>
                            <div class="input-group input-group-merge">
                              <span class="input-group-text"><i class="icon-base bx bx-charging-station"></i></span>
                              <input type="text" id="stationName{{ $station->id }}" name="name"
                                class="form-control" value="{{ $station->name }}" />
                            </div>
                          </div>
                          <div class="mb-6">
                            <label for="description{{ $station->id }}" class="form-label">Description</label>
                            <textarea id="description{{ $station->id }}" name="description" class="form-control">{{ $station->description }}</textarea>
                          </div>
                          <div class="mb-6">
                            <label for="address{{ $station->id }}" class="form-label">Address</label>
                            <input type="text" id="address{{ $station->id }}" name="address" class="form-control"
                              value="{{ $station->address }}" />
                          </div>
                          <div class="row g-4 mb-6">
                            <div class="col-md-6">
                              <label for="city{{ $station->id }}" class="form-label">City</label>
                              <input type="text" id="city{{ $station->id }}" name="city" class="form-control"
                                value="{{ $station->city }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="country{{ $station->id }}" class="form-label">Country</label>
                              <input type="text" id="country{{ $station->id }}" name="country"
                                class="form-control" value="{{ $station->country }}" />
                            </div>
                          </div>
                          <div class="row g-4 mb-6">
                            <div class="col-md-6">
                              <label for="latitude{{ $station->id }}" class="form-label">Latitude</label>
                              <input type="text" id="latitude{{ $station->id }}" name="latitude"
                                class="form-control" value="{{ $station->latitude }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="longitude{{ $station->id }}" class="form-label">Longitude</label>
                              <input type="text" id="longitude{{ $station->id }}" name="longitude"
                                class="form-control" value="{{ $station->longitude }}" />
                            </div>
                          </div>
                          <div class="mb-6">
                            <label for="facilities{{ $station->id }}" class="form-label">Facilities</label>
                            <textarea id="facilities{{ $station->id }}" name="facilities" class="form-control">{{ $station->facilities }}</textarea>
                          </div>
                          <div class="mb-6">
                            <label for="contactInfo{{ $station->id }}" class="form-label">Contact Information</label>
                            <input type="text" id="contactInfo{{ $station->id }}" name="contact_info"
                              class="form-control" value="{{ $station->contact_info }}" />
                          </div>
                          <div class="row g-4 mb-6">
                            <div class="col-md-6">
                              <label for="gracePeriod{{ $station->id }}" class="form-label">Grace Period
                                (minutes)
                              </label>
                              <input type="number" id="gracePeriod{{ $station->id }}"
                                name="default_grace_period_minutes" min="0" class="form-control"
                                value="{{ $station->default_grace_period_minutes }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="overstayFee{{ $station->id }}" class="form-label">Overstay Fee</label>
                              <input type="number" id="overstayFee{{ $station->id }}"
                                name="default_overstay_fee_amount" min="0" step="0.01" class="form-control"
                                value="{{ $station->default_overstay_fee_amount }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="overstayInterval{{ $station->id }}" class="form-label">Overstay Interval
                                (minutes)</label>
                              <input type="number" id="overstayInterval{{ $station->id }}"
                                name="default_overstay_interval_minutes" min="1" class="form-control"
                                value="{{ $station->default_overstay_interval_minutes }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="cancellationWindow{{ $station->id }}" class="form-label">Cancellation Window
                                (minutes)</label>
                              <input type="number" id="cancellationWindow{{ $station->id }}"
                                name="cancellation_window_minutes" min="0" class="form-control"
                                value="{{ $station->cancellation_window_minutes }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="noShowPeriod{{ $station->id }}" class="form-label">No-show Period
                                (days)</label>
                              <input type="number" id="noShowPeriod{{ $station->id }}" name="no_show_period_days"
                                min="0" class="form-control" value="{{ $station->no_show_period_days }}" />
                            </div>
                            <div class="col-md-6">
                              <label for="avgRating{{ $station->id }}" class="form-label">Average Rating</label>
                              <input type="number" id="avgRating{{ $station->id }}" name="avg_rating"
                                min="0" max="5" step="0.01" class="form-control"
                                value="{{ $station->avg_rating }}" />
                            </div>
                          </div>
                          <div class="mb-6">
                            <label for="status{{ $station->id }}" class="form-label">Status</label>
                            <select id="status{{ $station->id }}" name="status" class="form-select">
                              <option value="Active" {{ $station->status->value === 'Active' ? 'selected' : '' }}>Active
                              </option>
                              <option value="Inactive" {{ $station->status->value === 'Inactive' ? 'selected' : '' }}>
                                Inactive</option>
                              <option value="Maintenance"
                                {{ $station->status->value === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                          </div>
                          <fieldset>
                            <legend class="form-label">Working Hours</legend>
                            @for ($day = 0; $day < 7; $day++)
                              @php $hours = $station->stationWorkingHours->firstWhere('day_of_week', $day); @endphp
                              <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-3">{{ $dayNames[$day] }}</div>
                                <div class="col-md-4">
                                  <label class="form-label small mb-1">Open</label>
                                  <input type="time" name="working_hours[{{ $day }}][opens_at]"
                                    class="form-control"
                                    value="{{ old('working_hours.' . $day . '.opens_at', $hours?->opens_at ? \Illuminate\Support\Carbon::parse($hours->opens_at)->format('H:i') : '') }}" />
                                </div>
                                <div class="col-md-4">
                                  <label class="form-label small mb-1">Close</label>
                                  <input type="time" name="working_hours[{{ $day }}][closes_at]"
                                    class="form-control"
                                    value="{{ old('working_hours.' . $day . '.closes_at', $hours?->closes_at ? \Illuminate\Support\Carbon::parse($hours->closes_at)->format('H:i') : '') }}" />
                                </div>
                                <div class="col-md-1 d-flex flex-column align-items-center">
                                  <label class="form-label small mb-1">Open?</label>
                                  <input type="checkbox" name="working_hours[{{ $day }}][is_opened]"
                                    value="1" {{ $hours?->is_opened ?? true ? 'checked' : '' }}
                                    title="Is this station open on {{ $dayNames[$day] }}?" />
                                </div>
                              </div>
                            @endfor
                          </fieldset>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                {{-- /Edit Station Modal --}}

              @empty
                <tr>
                  <td colspan="10" class="text-center py-6">
                    @if (request('search'))
                      No stations match "{{ request('search') }}".
                    @else
                      No stations found.
                    @endif
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if (method_exists($stations, 'links'))
          <div class="card-body">
            {{ $stations->appends(request()->query())->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Add Station Modal --}}
  <div class="modal fade" id="addStationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="POST" action="{{ route('stations.store') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Add New Station</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-6">
              <label for="name" class="form-label">Station Name</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="icon-base bx bx-map-pin"></i></span>
                <input type="text" id="name" name="name"
                  class="form-control @error('name') is-invalid @enderror" placeholder="Downtown Charging Hub"
                  value="{{ old('name') }}" />
              </div>
              @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-6">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-6">
              <label for="address" class="form-label">Address</label>
              <input type="text" id="address" name="address"
                class="form-control @error('address') is-invalid @enderror" placeholder="123 Main St"
                value="{{ old('address') }}" />
              @error('address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="row g-4 mb-6">
              <div class="col-md-6">
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city"
                  class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" />
                @error('city')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="country" class="form-label">Country</label>
                <input type="text" id="country" name="country"
                  class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}" />
                @error('country')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="row g-4 mb-6">
              <div class="col-md-6">
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" id="latitude" name="latitude"
                  class="form-control @error('latitude') is-invalid @enderror" placeholder="33.5138"
                  value="{{ old('latitude') }}" />
                @error('latitude')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" id="longitude" name="longitude"
                  class="form-control @error('longitude') is-invalid @enderror" placeholder="36.2765"
                  value="{{ old('longitude') }}" />
                @error('longitude')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="mb-6">
              <label for="facilities" class="form-label">Facilities</label>
              <textarea id="facilities" name="facilities" class="form-control @error('facilities') is-invalid @enderror">{{ old('facilities') }}</textarea>
              @error('facilities')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-6">
              <label for="contactInfo" class="form-label">Contact Information</label>
              <input type="text" id="contactInfo" name="contact_info"
                class="form-control @error('contact_info') is-invalid @enderror" value="{{ old('contact_info') }}" />
              @error('contact_info')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="row g-4 mb-6">
              <div class="col-md-6"><label for="gracePeriod" class="form-label">Grace Period (minutes)</label><input
                  type="number" id="gracePeriod" name="default_grace_period_minutes" min="0"
                  class="form-control" value="{{ old('default_grace_period_minutes', 10) }}" /></div>
              <div class="col-md-6"><label for="overstayFee" class="form-label">Overstay Fee</label><input
                  type="number" id="overstayFee" name="default_overstay_fee_amount" min="0" step="0.01"
                  class="form-control" value="{{ old('default_overstay_fee_amount', 50) }}" /></div>
              <div class="col-md-6"><label for="overstayInterval" class="form-label">Overstay Interval
                  (minutes)</label><input type="number" id="overstayInterval" name="default_overstay_interval_minutes"
                  min="1" class="form-control" value="{{ old('default_overstay_interval_minutes', 5) }}" />
              </div>
              <div class="col-md-6"><label for="cancellationWindow" class="form-label">Cancellation Window
                  (minutes)</label><input type="number" id="cancellationWindow" name="cancellation_window_minutes"
                  min="0" class="form-control" value="{{ old('cancellation_window_minutes', 60) }}" /></div>
              <div class="col-md-6"><label for="noShowPeriod" class="form-label">No-show Period (days)</label><input
                  type="number" id="noShowPeriod" name="no_show_period_days" min="0" class="form-control"
                  value="{{ old('no_show_period_days', 30) }}" /></div>
              <div class="col-md-6"><label for="avgRating" class="form-label">Average Rating</label><input
                  type="number" id="avgRating" name="avg_rating" min="0" max="5" step="0.01"
                  class="form-control" value="{{ old('avg_rating', 0) }}" /></div>
            </div>
            <div class="mb-6">
              <label for="status" class="form-label">Status</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="Maintenance" {{ old('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance
                </option>
              </select>
              @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <fieldset>
              <legend class="form-label">Working Hours</legend>
              <div class="card mb-4 border-light bg-light-subtle">
                <div class="card-body">
                  <div class="d-flex flex-wrap align-items-end gap-3 mb-2">
                    <div>
                      <label class="form-label small mb-1">Apply same hours to all days</label>
                      <input type="time" id="allDaysOpenTime" class="form-control" />
                    </div>
                    <div>
                      <label class="form-label small mb-1">Close</label>
                      <input type="time" id="allDaysCloseTime" class="form-control" />
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-4">
                      <input type="checkbox" id="allDaysOpenCheckbox" checked />
                      <label for="allDaysOpenCheckbox" class="form-label small mb-0">Open for all days</label>
                    </div>
                    <div class="pt-4">
                      <button type="button" class="btn btn-sm btn-outline-primary" id="applyAllDaysHoursBtn">Apply to
                        all days</button>
                    </div>
                  </div>
                </div>
              </div>
              @for ($day = 0; $day < 7; $day++)
                <div class="row g-2 align-items-end mb-3">
                  <div class="col-md-3">{{ $dayNames[$day] }}</div>
                  <div class="col-md-4">
                    <label class="form-label small mb-1">Open</label>
                    <input type="time" name="working_hours[{{ $day }}][opens_at]"
                      class="form-control working-hours-open" />
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small mb-1">Close</label>
                    <input type="time" name="working_hours[{{ $day }}][closes_at]"
                      class="form-control working-hours-close" />
                  </div>
                  <div class="col-md-1 d-flex flex-column align-items-center">
                    <label class="form-label small mb-1">Open?</label>
                    <input type="checkbox" name="working_hours[{{ $day }}][is_opened]" value="1"
                      checked class="working-hours-opened" title="Is this station open on {{ $dayNames[$day] }}?" />
                  </div>
                </div>
              @endfor
            </fieldset>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                const allDaysOpenInput = document.getElementById('allDaysOpenTime');
                const allDaysCloseInput = document.getElementById('allDaysCloseTime');
                const allDaysChecked = document.getElementById('allDaysOpenCheckbox');
                const applyButton = document.getElementById('applyAllDaysHoursBtn');

                if (!applyButton) return;

                applyButton.addEventListener('click', function() {
                  const openValue = allDaysOpenInput.value;
                  const closeValue = allDaysCloseInput.value;
                  const checked = allDaysChecked.checked;

                  document.querySelectorAll('.working-hours-open').forEach(function(input) {
                    input.value = openValue;
                  });

                  document.querySelectorAll('.working-hours-close').forEach(function(input) {
                    input.value = closeValue;
                  });

                  document.querySelectorAll('.working-hours-opened').forEach(function(input) {
                    input.checked = checked;
                  });
                });
              });
            </script>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Add Station</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  {{-- /Add Station Modal --}}
@endsection
