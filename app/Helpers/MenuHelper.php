<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * Get menu groups for TailAdmin sidebar.
     */
    public static function getMenuGroups()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return [];
        }

        if ($currentUser->isPpic()) {
            return [
                [
                    'title' => 'MENU WMS',
                    'items' => [
                        [
                            'icon' => 'boxes',
                            'name' => 'Stok Barang',
                            'path' => '/items',
                        ],
                    ]
                ],
            ];
        }

        // Khusus Role QC (Quality Control): Hanya bisa melihat kategori MENU WMS -> Material Masuk
        if ($currentUser && $currentUser->isQc()) {
            return [
                [
                    'title' => 'MENU WMS',
                    'items' => [
                        [
                            'icon' => 'inbound',
                            'name' => 'Material Masuk',
                            'path' => '/material-in',
                        ],
                    ]
                ],
            ];
        }

        // Khusus Role Procurement: Purchase Order, Stok Barang, dan Rekap Project
        if ($currentUser && $currentUser->isProcurement()) {
            return [
                [
                    'title' => '',
                    'items' => [
                        [
                            'icon' => 'dashboard',
                            'name' => 'Dashboard',
                            'path' => '/',
                        ],
                    ]
                ],
                [
                    'title' => 'PENGADAAN (PROCUREMENT)',
                    'items' => [
                        [
                            'icon' => 'purchase-orders',
                            'name' => 'Purchase Order (PO)',
                            'path' => '/purchase-orders',
                        ],
                        [
                            'icon' => 'boxes',
                            'name' => 'Katalog & Stok Barang',
                            'path' => '/items',
                        ],
                        [
                            'icon' => 'projects',
                            'name' => 'Rekap Project',
                            'path' => '/projects-summary',
                        ],
                    ]
                ],
            ];
        }

        $menu = [
            // Standalone top menu item (Dashboard)
            [
                'title' => '',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/',
                    ],
                ]
            ],
            // Grup 1: MENU WMS (Operasional Gudang - Tanpa Menu PO)
            [
                'title' => 'MENU WMS',
                'items' => [
                    [
                        'icon' => 'boxes',
                        'name' => 'Stok Barang',
                        'path' => '/items',
                    ],
                    [
                        'icon' => 'inbound',
                        'name' => 'Material Masuk',
                        'path' => '/material-in',
                    ],
                    [
                        'icon' => 'outbound',
                        'name' => 'Material Keluar',
                        'path' => '/material-out',
                    ],
                    [
                        'icon' => 'categories',
                        'name' => 'Kategori Barang',
                        'path' => '/categories',
                    ],
                    [
                        'icon' => 'units',
                        'name' => 'Satuan Barang',
                        'path' => '/units',
                    ],
                    [
                        'icon' => 'projects',
                        'name' => 'Rekap Project',
                        'path' => '/projects-summary',
                    ],
                    [
                        'icon' => 'finish-goods',
                        'name' => 'Barang Jadi (Finish Good)',
                        'path' => '/finish-goods',
                    ],
                ]
            ],
            // Grup 2: PENCATATAN GUDANG
            [
                'title' => 'PENCATATAN & LAPORAN',
                'items' => [
                    [
                        'icon' => 'stock-logs',
                        'name' => 'Log Mutasi Barang',
                        'path' => '/stock-logs',
                    ],
                    [
                        'icon' => 'stock-opnames',
                        'name' => 'Stock Opname Harian',
                        'path' => '/stock-opnames',
                    ],
                    [
                        'icon' => 'report-incoming',
                        'name' => 'Laporan Inspeksi Incoming',
                        'path' => '/reports/incoming-inspection',
                    ],
                    [
                        'icon' => 'reject-material',
                        'name' => 'Material Rusak / Reject',
                        'path' => '/reports/reject-material',
                    ],
                    [
                        'icon' => 'slow-moving',
                        'name' => 'Analisis Slow & Dead Stock',
                        'path' => '/reports/slow-moving-stock',
                    ],
                    [
                        'icon' => 'outbound',
                        'name' => 'Laporan Pengeluaran',
                        'path' => '/reports/material-out',
                    ],
                    [
                        'icon' => 'report-warehouse',
                        'name' => 'Laporan Gudang Lengkap',
                        'path' => '/reports',
                    ],
                ]
            ],
        ];

        // Khusus Web Administrator: Memiliki akses mengelola Company Profile, Pengaturan System, dan Debugging
        // Role Gudang & PPIC disembunyikan dari menu ini.
        if ($currentUser && $currentUser->isWebAdmin()) {
            $menu[] = [
                'title' => 'COMPANY PROFILE',
                'items' => [
                    [
                        'icon' => 'company-profile',
                        'name' => 'Company Profile',
                        'path' => '/company-profile',
                    ],
                    [
                        'icon' => 'website-config',
                        'name' => 'Website Config',
                        'path' => '/website-theme',
                    ],
                ]
            ];

            $menu[] = [
                'title' => 'PENGATURAN SYSTEM',
                'items' => [
                    [
                        'icon' => 'users',
                        'name' => 'Manajemen Pengguna',
                        'path' => '/users',
                    ],
                    [
                        'icon' => 'trash',
                        'name' => 'Recycle Bin Data',
                        'path' => '/trash',
                    ],
                    [
                        'icon' => 'activity-logs',
                        'name' => 'Log Aktivitas',
                        'path' => '/activity-logs',
                    ],
                    [
                        'icon' => 'database',
                        'name' => 'Backup Database',
                        'path' => '/database-backup',
                    ],
                ]
            ];

            $menu[] = [
                'title' => 'DEBUGGING',
                'items' => [
                    [
                        'icon' => 'refresh',
                        'name' => 'Reset Data',
                        'path' => '/debugging/reset',
                    ],
                ]
            ];
        }

        return $menu;
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            // 0. Purchase Order (Procurement Clipboard)
            'purchase-orders' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>',

            // 1. Dashboard
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>',

            // 2. Stok Barang (Warehouse 3D Package Box)
            'boxes' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',

            // 3. Material Masuk (Arrow Down into Package Receiving)
            'inbound' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v11"></path><polyline points="8 10 12 14 16 10"></polyline><path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"></path><path d="M2 10h4"></path><path d="M18 10h4"></path></svg>',

            // 4. Material Keluar (Arrow Up out of Package Dispatch)
            'outbound' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 14V3"></path><polyline points="16 7 12 3 8 7"></polyline><path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"></path><path d="M2 10h4"></path><path d="M18 10h4"></path></svg>',

            // 5. Manajemen Project (Briefcase Blueprint)
            'projects' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><circle cx="12" cy="12" r="1"></circle></svg>',

            // 5b. Barang Jadi (Finish Good - Box Checkmark)
            'finish-goods' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="9 12 12 15 16 10"></polyline></svg>',

            // 6. Kategori Barang (Category Tag)
            'categories' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',

            // 6b. Satuan Barang (Measurement Unit Scale)
            'units' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path><path d="M2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path><path d="M7 21h10"></path><path d="M12 3v18"></path><path d="M3 7h18"></path></svg>',

            // 7. Log Mutasi Barang (Transfer Movement Arrows)
            'stock-logs' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',

            // 8. Stock Opname Harian (Audit Checklist Clipboard)
            'stock-opnames' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><polyline points="9 14 11 16 15 11"></polyline></svg>',

            // 9. Laporan Inspeksi Incoming (QC Inspection Shield)
            'report-incoming' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>',

            // 9b. Material Rusak / Reject (Alert Triangle)
            'reject-material' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',

            // 9c. Analisis Slow & Dead Stock (Clock / Inactivity)
            'slow-moving' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',

            // 10. Laporan Pengeluaran Cat (Paint Roller & Bucket)
            'report-cat' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="16" height="6" rx="2"></rect><path d="M18 6h3a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-8v3a2 2 0 0 0 2 2h0a2 2 0 0 1 2 2v2"></path><circle cx="6" cy="18" r="1.5" fill="currentColor"></circle></svg>',

            // 11. Laporan Pengeluaran Secondary Part (Mechanical Spare Part Cog)
            'report-secondary' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',

            // 12. Laporan Consumable (Welding & Consumable Flame Spark)
            'report-consumable' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>',

            // 13. Laporan Gudang Lengkap (Analytics Chart Document)
            'report-warehouse' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="8" y1="18" x2="8" y2="15"></line><line x1="16" y1="18" x2="16" y2="9"></line></svg>',

            // 14. Company Profile (Corporate Building)
            'company-profile' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M9 8h1"></path><path d="M9 12h1"></path><path d="M9 16h1"></path><path d="M14 8h1"></path><path d="M14 12h1"></path><path d="M14 16h1"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path></svg>',

            // 15. Website Config (Gear / Settings)
            'website-config' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'website-theme' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'gear' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',

            // 16. Manajemen Pengguna (Users Team)
            'users' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',

            // 17. Log Aktivitas (Activity Pulse Line)
            'activity-logs' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>',

            // 18. Backup Database (Server Cylinder Database)
            'database' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',

            // 18b. Trash / Recycle Bin
            'trash' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>',

            // 19. Reset Data Debugging (Circular Reset Arrows)
            'refresh' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>',

            // Legacy fallbacks
            'tables' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
            'forms' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v11"></path><polyline points="8 10 12 14 16 10"></polyline><path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"></path></svg>',
            'task' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',
            'charts' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="8" y1="18" x2="8" y2="15"></line><line x1="16" y1="18" x2="16" y2="9"></line></svg>',
            'ui-elements' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
            'palette' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"></path></svg>',
            'user-profile' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    }
}
