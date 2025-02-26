<x-filament-panels::page>
    <style>
        /* CSS para fixar a primeira coluna */
        .fixed-column {
            position: sticky;
            left: 0;
            z-index: 10;
        }
        /* CSS para fixar o cabeçalho da tabela */
        .fixed-header {
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .max-w-15dvh {
            max-width: 15dvh;
        }
        .max-w-20dvh {
            max-width: 20dvh;
        }
    </style>

    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between w-full">
        <div class="flex items-center gap-2 w-full">
            <div class="relative flex-1">
                <input
                        type="text"
                        placeholder="{{ __('Search') }}..."
                        wire:keyup.debounce="searchPermissions($event.target.value)"
                        wire:model="search"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                />
            </div>
            <button
                    type="button"
                    wire:click="savePermissions"
                    class="text-gray-100 flex-none inline-flex items-center px-4 py-2 rounded-md border border-transparent bg-primary-600 dark:bg-primary-500 font-semibold whitespace-nowrap hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            >
                {{ __('Save') }}
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-sm">
        <div class="overflow-y-auto rounded-lg shadow-sm" style="max-height: calc(60dvh);">
            <table class="w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="fixed-header bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="fixed-column border-b px-4 py-2 text-left font-bold text-gray-900 dark:text-gray-100  bg-gray-50 dark:bg-gray-700 max-w-15dvh">
                        {{ __('Roles') . ' / ' . __('Permissions') }}
                    </th>
                    @foreach($roles as $role)
                        <th class="border-b px-4 py-2 text-center font-bold text-gray-900 dark:text-gray-100  bg-gray-50 dark:bg-gray-700 truncate max-w-15dvh" title="{{$role->name}}">
                            {{ $role->name }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($permissions as $permission)
                    @if($search === '' || str_contains($permission->name, $search))
                        <tr class="hover:bg-gray-400/10">
                            <td class="fixed-column border-b px-4 py-2 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100 dark:border-gray-600 bg-gray-50 dark:!bg-gray-700 max-w-20dvh truncate" title="{{$permission->name}}">
                                {{ $permission->name }}
                            </td>
                            @foreach($roles as $role)
                                <td class="border-b px-4 py-2 text-center dark:border-gray-600">
                                    <label for="permission_{{ $permission->id }}_role_{{ $role->id }}" class="cursor-pointer">
                                        <input
                                                id="permission_{{ $permission->id }}_role_{{ $role->id }}"
                                                type="checkbox"
                                                class="form-checkbox h-5 w-5 text-primary-600 focus:ring-primary-500 rounded"
                                                wire:model.defer="selectedPermissions.{{ $role->id }}"
                                                value="{{ $permission->id }}"
                                        />
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 text-center text-gray-500">
                            {{ __('No permissions found') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
