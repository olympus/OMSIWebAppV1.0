<x-filament-panels::page>
    <div x-data="{ activeTab: 'send-request' }" class="space-y-6">
        
        <!-- Filament Style Tabs -->
        <div>
            <div class="fi-tabs flex overflow-x-auto gap-x-1 rounded-lg bg-white dark:bg-gray-900 p-2 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <button @click="activeTab = 'send-request'" 
                        :class="activeTab === 'send-request' ? 'fi-active bg-primary-600 text-white shadow' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="fi-tabs-item flex items-center gap-x-2 rounded-md px-4 py-2.5 text-sm font-medium transition">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    Send Request Data
                </button>
                <button @click="activeTab = 'download-request'" 
                        :class="activeTab === 'download-request' ? 'fi-active bg-success-600 text-white shadow' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="fi-tabs-item flex items-center gap-x-2 rounded-md px-4 py-2.5 text-sm font-medium transition">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Request Data
                </button>
                <button @click="activeTab = 'send-customer'" 
                        :class="activeTab === 'send-customer' ? 'fi-active bg-warning-600 text-white shadow' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="fi-tabs-item flex items-center gap-x-2 rounded-md px-4 py-2.5 text-sm font-medium transition">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Send Customer Data
                </button>
            </div>
        </div><br>

        <!-- Tab Content -->
        <div>
            
            <!-- Send Request Data Tab -->
            <div x-show="activeTab === 'send-request'" x-transition>
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="fi-section-header flex items-center gap-x-3 px-6 py-4">
                        <div class="grid flex-1 gap-y-1">
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Send Request Data via Email
                            </h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                Filter and send request data to specified email addresses
                            </p>
                        </div>
                    </div>
                    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                        <div class="fi-section-content p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            From Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="from_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            To Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="to_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Request Type</span>
                                    </label>
                                    <select wire:model.live="request_type" 
                                            class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6"
                                            style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                        <option value="all">All</option>
                                        <option value="service">Service</option>
                                        <option value="academic">Academic</option>
                                        <option value="enquiry">Enquiry</option>
                                    </select>
                                </div><br>
                                @if($request_type !== 'all' && $request_type)
                                    <div class="fi-form-field-wrp">
                                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Sub Type</span>
                                        </label>
                                        <select wire:model="sub_type" 
                                                class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6"
                                                style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                            <option value="all">All</option>
                                            @foreach($this->getSubTypeOptions($request_type) as $value => $label)
                                                @if($value !== 'all')
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div><br>
                                    <div class="fi-form-field-wrp">
                                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Status</span>
                                        </label>
                                        <select wire:model="status" 
                                                class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6"
                                                style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                            <option value="all">All</option>
                                            @foreach($this->getStatusOptions($request_type) as $value => $label)
                                                @if($value !== 'all')
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div><br>
                                @endif
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            Email <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <select wire:model="email" 
                                            class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6"
                                            style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                        <option value="">Select Email</option>
                                        @foreach($this->getEmailOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div><br>
                                <div class="fi-form-field-wrp md:col-span-2">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            CC Email <span class="text-sm text-gray-500 dark:text-gray-400">(comma separated)</span>
                                        </span>
                                    </label><br>
                                    <textarea wire:model="cc_email" rows="3" 
                                              class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500 sm:text-sm sm:leading-6"
                                              style="width: 100% !important; border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;"
                                              placeholder="email1@example.com, email2@example.com"></textarea>
                                </div>
                            </div>
                            
                            <!-- Submit Button for Send Request Data -->
                            <div class="mt-6 flex justify-end"><br>
                                <button wire:click="mountAction('send_request_data')" 
                                        wire:loading.attr="disabled"
                                        type="button"
                                        style="background-color: #eab308; color: white; padding: 0.625rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.375rem;"
                                        onmouseover="this.style.backgroundColor='#ca8a04'"
                                        onmouseout="this.style.backgroundColor='#eab308'">
                                    <svg wire:loading.remove wire:target="mountAction('send_request_data')" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    <svg wire:loading wire:target="mountAction('send_request_data')" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="mountAction('send_request_data')">Send Request Data</span>
                                    <span wire:loading wire:target="mountAction('send_request_data')">Sending...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Request Data Tab -->
            <div x-show="activeTab === 'download-request'" x-transition x-cloak>
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="fi-section-header flex items-center gap-x-3 px-6 py-4">
                        <div class="grid flex-1 gap-y-1">
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Download Request Data
                            </h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                Filter and download request data as Excel file
                            </p>
                        </div>
                    </div>
                    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                        <div class="fi-section-content p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            From Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="from_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-success-600 focus:ring-1 focus:ring-inset focus:ring-success-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-success-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            To Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="to_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-success-600 focus:ring-1 focus:ring-inset focus:ring-success-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-success-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Request Type</span>
                                    </label>
                                    <select wire:model.live="request_type" 
                                            class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-success-600 focus:ring-1 focus:ring-inset focus:ring-success-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-success-500 sm:text-sm sm:leading-6"
                                            style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                        <option value="all">All</option>
                                        <option value="service">Service</option>
                                        <option value="academic">Academic</option>
                                        <option value="enquiry">Enquiry</option>
                                    </select>
                                </div><br>
                                @if($request_type !== 'all' && $request_type)
                                    <div class="fi-form-field-wrp">
                                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Sub Type</span>
                                        </label>
                                        <select wire:model="sub_type" 
                                                class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-success-600 focus:ring-1 focus:ring-inset focus:ring-success-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-success-500 sm:text-sm sm:leading-6"
                                                style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                            <option value="all">All</option>
                                            @foreach($this->getSubTypeOptions($request_type) as $value => $label)
                                                @if($value !== 'all')
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div><br>
                                    <div class="fi-form-field-wrp">
                                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Status</span>
                                        </label>
                                        <select wire:model="status" 
                                                class="fi-select-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-success-600 focus:ring-1 focus:ring-inset focus:ring-success-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-success-500 sm:text-sm sm:leading-6"
                                                style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;">
                                            <option value="all">All</option>
                                            @foreach($this->getStatusOptions($request_type) as $value => $label)
                                                @if($value !== 'all')
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div><br>
                                @endif
                            </div>
                            
                            <!-- Submit Button for Download Request Data -->
                            <div class="mt-6 flex justify-end">
                                <button wire:click="mountAction('download_request_data')" 
                                        wire:loading.attr="disabled"
                                        type="button"
                                        style="background-color: #16a34a; color: white; padding: 0.625rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.375rem;"
                                        onmouseover="this.style.backgroundColor='#15803d'"
                                        onmouseout="this.style.backgroundColor='#16a34a'">
                                    <svg wire:loading.remove wire:target="mountAction('download_request_data')" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <svg wire:loading wire:target="mountAction('download_request_data')" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="mountAction('download_request_data')">Download Request Data</span>
                                    <span wire:loading wire:target="mountAction('download_request_data')">Downloading...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send Customer Data Tab -->
            <div x-show="activeTab === 'send-customer'" x-transition x-cloak>
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="fi-section-header flex items-center gap-x-3 px-6 py-4">
                        <div class="grid flex-1 gap-y-1">
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Send Customer Data via Email
                            </h3>
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                Export and send customer data for specified date range
                            </p>
                        </div>
                    </div>
                    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                        <div class="fi-section-content p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            From Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="from_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-warning-600 focus:ring-1 focus:ring-inset focus:ring-warning-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-warning-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            To Date <span class="text-danger-600 dark:text-danger-400">*</span>
                                        </span>
                                    </label>
                                    <input type="date" wire:model="to_date" 
                                           class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-warning-600 focus:ring-1 focus:ring-inset focus:ring-warning-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-warning-500 sm:text-sm sm:leading-6" 
                                           max="{{ date('Y-m-d') }}"
                                           style="border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;" />
                                </div><br>
                                <div class="fi-form-field-wrp md:col-span-2">
                                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            CC Email <span class="text-danger-600 dark:text-danger-400">*</span> <span class="text-sm text-gray-500 dark:text-gray-400">(comma separated)</span>
                                        </span>
                                    </label><br>
                                    <textarea wire:model="cc_email" rows="3" 
                                              class="fi-input block w-full border-gray-300 rounded-lg shadow-sm transition duration-75 focus:border-warning-600 focus:ring-1 focus:ring-inset focus:ring-warning-600 disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-warning-500 sm:text-sm sm:leading-6"
                                              style="width: 100% !important; border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem;"
                                              placeholder="email1@example.com, email2@example.com"></textarea>
                                </div>
                            </div>
                            
                            <!-- Submit Button for Send Customer Data -->
                            <div class="mt-6 flex justify-end">
                                <button wire:click="mountAction('send_customer_data')" 
                                        wire:loading.attr="disabled"
                                        type="button"
                                        style="background-color: #f97316; color: white; padding: 0.625rem 1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.375rem;"
                                        onmouseover="this.style.backgroundColor='#ea580c'"
                                        onmouseout="this.style.backgroundColor='#f97316'">
                                    <svg wire:loading.remove wire:target="mountAction('send_customer_data')" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    <svg wire:loading wire:target="mountAction('send_customer_data')" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="mountAction('send_customer_data')">Send Customer Data</span>
                                    <span wire:loading wire:target="mountAction('send_customer_data')">Sending...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>
