@props([
    'active' => false,
    'activeChildItems' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'badgeTooltip' => null,
    'childItems' => [],
    'first' => false,
    'grouped' => false,
    'icon' => null,
    'last' => false,
    'shouldOpenUrlInNewTab' => false,
    'sidebarCollapsible' => true,
    'subGrouped' => false,
    'subNavigation' => false,
    'url',
])

@php
    $sidebarCollapsible = $sidebarCollapsible && filament()->isSidebarCollapsibleOnDesktop();
    $hasCollapsibleChildren = is_countable($childItems) && count($childItems) > 0;
@endphp

<li
    @if ($hasCollapsibleChildren)
        x-data="{
            subListOpen: @json($active || $activeChildItems),
        }"
        x-effect="
            if (@js($active || $activeChildItems)) {
                subListOpen = true
            }
        "
    @endif
    {{
        $attributes->class([
            'fi-sidebar-item',
            'fi-active' => $active,
            'fi-sidebar-item-has-active-child-items' => $activeChildItems,
            'fi-sidebar-item-has-url' => filled($url),
            'fi-sidebar-item-has-collapsible-children' => $hasCollapsibleChildren,
        ])
    }}
>
    @if ($hasCollapsibleChildren)
        <div @class([
            'flex w-full min-w-0 items-center gap-0.5',
            'fi-sidebar-item-with-children-ctn' => ! $subGrouped,
        ])>
            <a
                {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
                x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
                @if ($sidebarCollapsible && (! $subNavigation))
                    x-data="{ tooltip: false }"
                    x-effect="
                        tooltip = $store.sidebar.isOpen
                            ? false
                            : {
                                  content: @js($slot->toHtml()),
                                  placement: document.dir === 'rtl' ? 'left' : 'right',
                                  theme: $store.theme,
                              }
                    "
                    x-tooltip.html="tooltip"
                @endif
                @class([
                    'fi-sidebar-item-btn min-w-0 flex-1',
                    'fi-sidebar-item-parent-with-children' => ! $subGrouped,
                ])
            >
                @if (filled($icon) && ((! $subGrouped) || ($sidebarCollapsible && (! $subNavigation))) && (! ($hasCollapsibleChildren && (! $subGrouped))))
                    {{
                        \Filament\Support\generate_icon_html(($active && $activeIcon) ? $activeIcon : $icon, attributes: (new \Illuminate\View\ComponentAttributeBag([
                            'x-show' => ($subGrouped && $sidebarCollapsible) ? '! $store.sidebar.isOpen' : false,
                        ]))->class(['fi-sidebar-item-icon']), size: \Filament\Support\Enums\IconSize::Large)
                    }}
                @endif

                @if ((blank($icon) && $grouped) || $subGrouped)
                    <div
                        @if (filled($icon) && $subGrouped && $sidebarCollapsible && (! $subNavigation))
                            x-show="$store.sidebar.isOpen"
                        @endif
                        class="fi-sidebar-item-grouped-border"
                    >
                        @if (! $first)
                            <div
                                class="fi-sidebar-item-grouped-border-part-not-first"
                            ></div>
                        @endif

                        @if (! $last)
                            <div
                                class="fi-sidebar-item-grouped-border-part-not-last"
                            ></div>
                        @endif

                        <div class="fi-sidebar-item-grouped-border-part"></div>
                    </div>
                @endif

                <span
                    @if ($sidebarCollapsible && (! $subNavigation))
                        x-show="$store.sidebar.isOpen"
                        x-transition:enter="fi-transition-enter"
                        x-transition:enter-start="fi-transition-enter-start"
                        x-transition:enter-end="fi-transition-enter-end"
                    @endif
                    @class([
                        'fi-sidebar-item-label',
                        'fi-sidebar-item-parent-label text-sm font-medium text-gray-600 dark:text-gray-400' => $hasCollapsibleChildren && (! $subGrouped),
                    ])
                >
                    {{ $slot }}
                </span>

                @if (filled($badge))
                    <span
                        @if ($sidebarCollapsible && (! $subNavigation))
                            x-show="$store.sidebar.isOpen"
                            x-transition:enter="fi-transition-enter"
                            x-transition:enter-start="fi-transition-enter-start"
                            x-transition:enter-end="fi-transition-enter-end"
                        @endif
                        class="fi-sidebar-item-badge-ctn"
                    >
                        <x-filament::badge
                            :color="$badgeColor"
                            :tooltip="$badgeTooltip"
                        >
                            {{ $badge }}
                        </x-filament::badge>
                    </span>
                @endif
            </a>

            @if ($sidebarCollapsible && (! $subNavigation))
                <div
                    x-show="$store.sidebar.isOpen"
                    x-transition:enter="fi-transition-enter"
                    x-transition:enter-start="fi-transition-enter-start"
                    x-transition:enter-end="fi-transition-enter-end"
                    class="fi-sidebar-item-child-collapse-ctn ms-auto shrink-0"
                >
                    <x-filament::icon-button
                        color="gray"
                        :icon="\Filament\Support\Icons\Heroicon::ChevronUp"
                        :icon-alias="\Filament\View\PanelsIconAlias::SIDEBAR_GROUP_COLLAPSE_BUTTON"
                        :label="__('Toggle sub-menu')"
                        x-on:click="subListOpen = ! subListOpen"
                        x-bind:class="{ 'rotate-180': ! subListOpen }"
                        class="fi-sidebar-item-child-collapse-btn transition-transform"
                    />
                </div>
            @endif
        </div>
    @else
        <a
            {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
            x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
            @if ($sidebarCollapsible && (! $subNavigation))
                x-data="{ tooltip: false }"
                x-effect="
                    tooltip = $store.sidebar.isOpen
                        ? false
                        : {
                              content: @js($slot->toHtml()),
                              placement: document.dir === 'rtl' ? 'left' : 'right',
                              theme: $store.theme,
                          }
                "
                x-tooltip.html="tooltip"
            @endif
            class="fi-sidebar-item-btn"
        >
            @if (filled($icon) && ((! $subGrouped) || ($sidebarCollapsible && (! $subNavigation))))
                {{
                    \Filament\Support\generate_icon_html(($active && $activeIcon) ? $activeIcon : $icon, attributes: (new \Illuminate\View\ComponentAttributeBag([
                        'x-show' => ($subGrouped && $sidebarCollapsible) ? '! $store.sidebar.isOpen' : false,
                    ]))->class(['fi-sidebar-item-icon']), size: \Filament\Support\Enums\IconSize::Large)
                }}
            @endif

            @if ((blank($icon) && $grouped) || $subGrouped)
                <div
                    @if (filled($icon) && $subGrouped && $sidebarCollapsible && (! $subNavigation))
                        x-show="$store.sidebar.isOpen"
                    @endif
                    class="fi-sidebar-item-grouped-border"
                >
                    @if (! $first)
                        <div
                            class="fi-sidebar-item-grouped-border-part-not-first"
                        ></div>
                    @endif

                    @if (! $last)
                        <div
                            class="fi-sidebar-item-grouped-border-part-not-last"
                        ></div>
                    @endif

                    <div class="fi-sidebar-item-grouped-border-part"></div>
                </div>
            @endif

            <span
                @if ($sidebarCollapsible && (! $subNavigation))
                    x-show="$store.sidebar.isOpen"
                    x-transition:enter="fi-transition-enter"
                    x-transition:enter-start="fi-transition-enter-start"
                    x-transition:enter-end="fi-transition-enter-end"
                @endif
                class="fi-sidebar-item-label"
            >
                {{ $slot }}
            </span>

            @if (filled($badge))
                <span
                    @if ($sidebarCollapsible && (! $subNavigation))
                        x-show="$store.sidebar.isOpen"
                        x-transition:enter="fi-transition-enter"
                        x-transition:enter-start="fi-transition-enter-start"
                        x-transition:enter-end="fi-transition-enter-end"
                    @endif
                    class="fi-sidebar-item-badge-ctn"
                >
                    <x-filament::badge
                        :color="$badgeColor"
                        :tooltip="$badgeTooltip"
                    >
                        {{ $badge }}
                    </x-filament::badge>
                </span>
            @endif
        </a>
    @endif

    @if ($hasCollapsibleChildren)
        <ul
            class="fi-sidebar-sub-group-items ps-2"
            x-show="subListOpen"
            x-collapse.duration.200ms
            @if ($sidebarCollapsible)
                x-transition:enter="fi-transition-enter"
                x-transition:enter-start="fi-transition-enter-start"
                x-transition:enter-end="fi-transition-enter-end"
            @endif
        >
            @foreach ($childItems as $childItem)
                @php
                    $isChildItemChildItemsActive = $childItem->isChildItemsActive();
                    $isChildActive = (! $isChildItemChildItemsActive) && $childItem->isActive();
                    $childItemActiveIcon = $childItem->getActiveIcon();
                    $childItemBadge = $childItem->getBadge();
                    $childItemBadgeColor = $childItem->getBadgeColor();
                    $childItemBadgeTooltip = $childItem->getBadgeTooltip();
                    $childItemIcon = $childItem->getIcon();
                    $shouldChildItemOpenUrlInNewTab = $childItem->shouldOpenUrlInNewTab();
                    $childItemUrl = $childItem->getUrl();
                @endphp

                <x-filament-panels::sidebar.item
                    :active="$isChildActive"
                    :active-child-items="$isChildItemChildItemsActive"
                    :active-icon="$childItemActiveIcon"
                    :badge="$childItemBadge"
                    :badge-color="$childItemBadgeColor"
                    :badge-tooltip="$childItemBadgeTooltip"
                    :icon="$childItemIcon"
                    :should-open-url-in-new-tab="$shouldChildItemOpenUrlInNewTab"
                    :sidebar-collapsible="$sidebarCollapsible"
                    :sub-navigation="$subNavigation"
                    :url="$childItemUrl"
                >
                    {{ $childItem->getLabel() }}
                </x-filament-panels::sidebar.item>
            @endforeach
        </ul>
    @endif
</li>
