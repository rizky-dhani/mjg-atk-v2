<?php

return [

    'builder' => [

        'actions' => [

            'clone' => [
                'label' => 'Klon',
            ],

            'add' => [

                'label' => 'Tambah ke :label',

                'modal' => [

                    'heading' => 'Tambah ke :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Tambah',
                        ],

                    ],

                ],

            ],

            'add_between' => [

                'label' => 'Sisipkan di antara blok',

                'modal' => [

                    'heading' => 'Tambah ke :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Tambah',
                        ],

                    ],

                ],

            ],

            'delete' => [
                'label' => 'Hapus',
            ],

            'edit' => [

                'label' => 'Edit',

                'modal' => [

                    'heading' => 'Edit blok',

                    'actions' => [

                        'save' => [
                            'label' => 'Simpan perubahan',
                        ],

                    ],

                ],

            ],

            'reorder' => [
                'label' => 'Pindahkan',
            ],

            'move_down' => [
                'label' => 'Pindah ke bawah',
            ],

            'move_up' => [
                'label' => 'Pindah ke atas',
            ],

            'collapse' => [
                'label' => 'Ciutkan',
            ],

            'expand' => [
                'label' => 'Perluas',
            ],

            'collapse_all' => [
                'label' => 'Ciutkan semua',
            ],

            'expand_all' => [
                'label' => 'Perluas semua',
            ],

        ],

    ],

    'checkbox_list' => [

        'actions' => [

            'deselect_all' => [
                'label' => 'Batal pilih semua',
            ],

            'select_all' => [
                'label' => 'Pilih semua',
            ],

        ],

    ],

    'file_upload' => [

        'editor' => [

            'actions' => [

                'cancel' => [
                    'label' => 'Batal',
                ],

                'drag_crop' => [
                    'label' => 'Mode seret "crop"',
                ],

                'drag_move' => [
                    'label' => 'Mode seret "move"',
                ],

                'flip_horizontal' => [
                    'label' => 'Balik gambar secara horizontal',
                ],

                'flip_vertical' => [
                    'label' => 'Balik gambar secara vertikal',
                ],

                'move_down' => [
                    'label' => 'Pindahkan gambar ke bawah',
                ],

                'move_left' => [
                    'label' => 'Pindahkan gambar ke kiri',
                ],

                'move_right' => [
                    'label' => 'Pindahkan gambar ke kanan',
                ],

                'move_up' => [
                    'label' => 'Pindahkan gambar ke atas',
                ],

                'reset' => [
                    'label' => 'Atur ulang',
                ],

                'rotate_left' => [
                    'label' => 'Putar gambar ke kiri',
                ],

                'rotate_right' => [
                    'label' => 'Putar gambar ke kanan',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Atur rasio aspek ke :ratio',
                ],

                'save' => [
                    'label' => 'Simpan',
                ],

                'zoom_100' => [
                    'label' => 'Perbesar gambar ke 100%',
                ],

                'zoom_in' => [
                    'label' => 'Perbesar',
                ],

                'zoom_out' => [
                    'label' => 'Perkecil',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Tinggi',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Rotasi',
                    'unit' => 'derajat',
                ],

                'width' => [
                    'label' => 'Lebar',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Rasio aspek',

                'no_fixed' => [
                    'label' => 'Bebas',
                ],

            ],

            'svg' => [

                'messages' => [
                    'confirmation' => 'Mengedit file SVG tidak disarankan karena dapat mengakibatkan penurunan kualitas saat scaling.\n Apakah Anda yakin ingin melanjutkan?',
                    'disabled' => 'Pengeditan file SVG dinonaktifkan karena dapat mengakibatkan penurunan kualitas saat scaling.',
                ],

            ],

        ],

    ],

    'key_value' => [

        'actions' => [

            'add' => [
                'label' => 'Tambah baris',
            ],

            'delete' => [
                'label' => 'Hapus baris',
            ],

            'reorder' => [
                'label' => 'Atur ulang baris',
            ],

        ],

        'fields' => [

            'key' => [
                'label' => 'Kunci',
            ],

            'value' => [
                'label' => 'Nilai',
            ],

        ],

    ],

    'markdown_editor' => [

        'file_attachments_accepted_file_types_message' => 'File yang diunggah harus bertipe: :values.',

        'file_attachments_max_size_message' => 'File yang diunggah tidak boleh lebih dari :max kilobyte.',

        'tools' => [
            'attach_files' => 'Lampirkan file',
            'blockquote' => 'Kutipan blok',
            'bold' => 'Tebal',
            'bullet_list' => 'Daftar bullet',
            'code_block' => 'Blok kode',
            'heading' => 'Judul',
            'italic' => 'Miring',
            'link' => 'Tautan',
            'ordered_list' => 'Daftar bernomor',
            'redo' => 'Ulangi',
            'strike' => 'Coret',
            'table' => 'Tabel',
            'undo' => 'Urungkan',
        ],

    ],

    'modal_table_select' => [

        'actions' => [

            'select' => [

                'label' => 'Pilih',

                'actions' => [

                    'select' => [
                        'label' => 'Pilih',
                    ],

                ],

            ],

        ],

    ],

    'radio' => [

        'boolean' => [
            'true' => 'Ya',
            'false' => 'Tidak',
        ],

    ],

    'repeater' => [

        'actions' => [

            'add' => [
                'label' => 'Tambah ke :label',
            ],

            'add_between' => [
                'label' => 'Sisipkan di antara',
            ],

            'delete' => [
                'label' => 'Hapus',
            ],

            'clone' => [
                'label' => 'Klon',
            ],

            'reorder' => [
                'label' => 'Pindahkan',
            ],

            'move_down' => [
                'label' => 'Pindah ke bawah',
            ],

            'move_up' => [
                'label' => 'Pindah ke atas',
            ],

            'collapse' => [
                'label' => 'Ciutkan',
            ],

            'expand' => [
                'label' => 'Perluas',
            ],

            'collapse_all' => [
                'label' => 'Ciutkan semua',
            ],

            'expand_all' => [
                'label' => 'Perluas semua',
            ],

        ],

    ],

    'rich_editor' => [

        'actions' => [

            'attach_files' => [

                'label' => 'Unggah file',

                'modal' => [

                    'heading' => 'Unggah file',

                    'form' => [

                        'file' => [

                            'label' => [
                                'new' => 'File',
                                'existing' => 'Ganti file',
                            ],

                        ],

                        'alt' => [

                            'label' => [
                                'new' => 'Teks alt',
                                'existing' => 'Ubah teks alt',
                            ],

                        ],

                    ],

                ],

            ],

            'custom_block' => [

                'modal' => [

                    'actions' => [

                        'insert' => [
                            'label' => 'Sisipkan',
                        ],

                        'save' => [
                            'label' => 'Simpan',
                        ],

                    ],

                ],

            ],

            'grid' => [

                'label' => 'Grid',

                'modal' => [

                    'heading' => 'Grid',

                    'form' => [

                        'preset' => [

                            'label' => 'Preset',

                            'placeholder' => 'Tidak ada',

                            'options' => [
                                'two' => 'Dua',
                                'three' => 'Tiga',
                                'four' => 'Empat',
                                'five' => 'Lima',
                                'two_start_third' => 'Dua (Mulai Sepertiga)',
                                'two_end_third' => 'Dua (Akhir Sepertiga)',
                                'two_start_fourth' => 'Dua (Mulai Seperempat)',
                                'two_end_fourth' => 'Dua (Akhir Seperempat)',
                            ],
                        ],

                        'columns' => [
                            'label' => 'Kolom',
                        ],

                        'from_breakpoint' => [

                            'label' => 'Dari breakpoint',

                            'options' => [
                                'default' => 'Semua',
                                'sm' => 'Kecil',
                                'md' => 'Sedang',
                                'lg' => 'Besar',
                                'xl' => 'Sangat besar',
                                '2xl' => 'Dua kali sangat besar',
                            ],

                        ],

                        'is_asymmetric' => [
                            'label' => 'Dua kolom asimetris',
                        ],

                        'start_span' => [
                            'label' => 'Span awal',
                        ],

                        'end_span' => [
                            'label' => 'Span akhir',
                        ],

                    ],

                ],

            ],

            'link' => [

                'label' => 'Tautan',

                'modal' => [

                    'heading' => 'Tautan',

                    'form' => [

                        'url' => [
                            'label' => 'URL',
                        ],

                        'should_open_in_new_tab' => [
                            'label' => 'Buka di tab baru',
                        ],

                    ],

                ],

            ],

            'text_color' => [

                'label' => 'Warna teks',

                'modal' => [

                    'heading' => 'Warna teks',

                    'form' => [

                        'color' => [
                            'label' => 'Warna',

                            'options' => [
                                'slate' => 'Slate',
                                'gray' => 'Abu-abu',
                                'zinc' => 'Zinc',
                                'neutral' => 'Netral',
                                'stone' => 'Batu',
                                'mauve' => 'Mauve',
                                'olive' => 'Zaitun',
                                'mist' => 'Kabut',
                                'taupe' => 'Taupe',
                                'red' => 'Merah',
                                'orange' => 'Oranye',
                                'amber' => 'Amber',
                                'yellow' => 'Kuning',
                                'lime' => 'Lime',
                                'green' => 'Hijau',
                                'emerald' => 'Zamrud',
                                'teal' => 'Teal',
                                'cyan' => 'Cyan',
                                'sky' => 'Langit',
                                'blue' => 'Biru',
                                'indigo' => 'Indigo',
                                'violet' => 'Violet',
                                'purple' => 'Ungu',
                                'fuchsia' => 'Fuchsia',
                                'pink' => 'Merah muda',
                                'rose' => 'Mawar',
                            ],
                        ],

                        'custom_color' => [
                            'label' => 'Warna kustom',
                        ],

                    ],

                ],

            ],

        ],

        'file_attachments_accepted_file_types_message' => 'File yang diunggah harus bertipe: :values.',

        'file_attachments_max_size_message' => 'File yang diunggah tidak boleh lebih dari :max kilobyte.',

        'no_merge_tag_search_results_message' => 'Tidak ada hasil merge tag.',

        'mentions' => [
            'no_options_message' => 'Tidak ada opsi tersedia.',
            'no_search_results_message' => 'Tidak ada hasil yang cocok dengan pencarian Anda.',
            'search_prompt' => 'Mulai mengetik untuk mencari...',
            'searching_message' => 'Mencari...',
        ],

        'tools' => [
            'align_center' => 'Rata tengah',
            'align_end' => 'Rata kanan',
            'align_justify' => 'Rata kiri-kanan',
            'align_start' => 'Rata kiri',
            'attach_files' => 'Lampirkan file',
            'blockquote' => 'Kutipan blok',
            'bold' => 'Tebal',
            'bullet_list' => 'Daftar bullet',
            'clear_formatting' => 'Hapus format',
            'code' => 'Kode',
            'code_block' => 'Blok kode',
            'custom_blocks' => 'Blok',
            'details' => 'Detail',
            'h1' => 'Judul',
            'h2' => 'Judul 2',
            'h3' => 'Judul 3',
            'h4' => 'Judul 4',
            'h5' => 'Judul 5',
            'h6' => 'Judul 6',
            'grid' => 'Grid',
            'grid_delete' => 'Hapus grid',
            'highlight' => 'Sorot',
            'horizontal_rule' => 'Garis horizontal',
            'italic' => 'Miring',
            'lead' => 'Teks lead',
            'link' => 'Tautan',
            'merge_tags' => 'Tag gabungan',
            'ordered_list' => 'Daftar bernomor',
            'paragraph' => 'Paragraf',
            'redo' => 'Ulangi',
            'small' => 'Teks kecil',
            'strike' => 'Coret',
            'subscript' => 'Subskrip',
            'superscript' => 'Superskrip',
            'table' => 'Tabel',
            'table_delete' => 'Hapus tabel',
            'table_add_column_before' => 'Tambah kolom sebelum',
            'table_add_column_after' => 'Tambah kolom sesudah',
            'table_delete_column' => 'Hapus kolom',
            'table_add_row_before' => 'Tambah baris di atas',
            'table_add_row_after' => 'Tambah baris di bawah',
            'table_delete_row' => 'Hapus baris',
            'table_merge_cells' => 'Gabung sel',
            'table_split_cell' => 'Pisah sel',
            'table_toggle_header_row' => 'Alihkan baris header',
            'table_toggle_header_cell' => 'Alihkan sel header',
            'text_color' => 'Warna teks',
            'underline' => 'Garis bawah',
            'undo' => 'Urungkan',
        ],

        'uploading_file_message' => 'Mengunggah file...',

    ],

    'select' => [

        'actions' => [

            'create_option' => [

                'label' => 'Buat',

                'modal' => [

                    'heading' => 'Buat',

                    'actions' => [

                        'create' => [
                            'label' => 'Buat',
                        ],

                        'create_another' => [
                            'label' => 'Buat & buat lagi',
                        ],

                    ],

                ],

            ],

            'edit_option' => [

                'label' => 'Edit',

                'modal' => [

                    'heading' => 'Edit',

                    'actions' => [

                        'save' => [
                            'label' => 'Simpan',
                        ],

                    ],

                ],

            ],

        ],

        'boolean' => [
            'true' => 'Ya',
            'false' => 'Tidak',
        ],

        'loading_message' => 'Memuat...',

        'max_items_message' => 'Hanya :count yang dapat dipilih.',

        'no_options_message' => 'Tidak ada opsi tersedia.',

        'no_search_results_message' => 'Tidak ada opsi yang cocok dengan pencarian Anda.',

        'placeholder' => 'Pilih opsi',

        'searching_message' => 'Mencari...',

        'search_prompt' => 'Mulai mengetik untuk mencari...',

    ],

    'tags_input' => [

        'actions' => [

            'delete' => [
                'label' => 'Hapus',
            ],

        ],

        'placeholder' => 'Tag baru',

    ],

    'text_input' => [

        'actions' => [

            'copy' => [
                'label' => 'Salin',
                'message' => 'Disalin',
            ],

            'hide_password' => [
                'label' => 'Sembunyikan kata sandi',
            ],

            'show_password' => [
                'label' => 'Tampilkan kata sandi',
            ],

        ],

    ],

    'toggle_buttons' => [

        'boolean' => [
            'true' => 'Ya',
            'false' => 'Tidak',
        ],

    ],

];
