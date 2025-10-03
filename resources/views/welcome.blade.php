<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skolabs - LMS Cerdas dengan Analisis Akademik Berbasis AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        /* Menggunakan font Inter sebagai default */
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Efek gradien halus pada hero section */
        .hero-gradient {
            background: radial-gradient(circle at top, rgba(240, 244, 255, 0.8), transparent 70%);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

    <header class="bg-white/80 backdrop-blur-lg fixed top-0 left-0 right-0 z-10 border-b border-slate-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-indigo-600">Skolabs</a>
            <nav class="hidden md:flex space-x-8 items-center">
                <a href="#fitur" class="text-slate-600 hover:text-indigo-600 transition-colors">Fitur</a>
                <a href="#testimoni" class="text-slate-600 hover:text-indigo-600 transition-colors">Testimoni</a>
                <a href="#tentang" class="text-slate-600 hover:text-indigo-600 transition-colors">Tentang Proyek</a>
            </nav>
            <div class="flex items-center space-x-4">
                <a href="https://github.com/" target="_blank"
                    class="hidden sm:flex items-center space-x-2 text-slate-600 hover:text-indigo-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                        </path>
                    </svg>
                    <span>GitHub</span>
                </a>
                <a href="/login"
                    class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition-all shadow-sm">Mulai
                    Gunakan</a>
            </div>
        </div>
    </header>

    <main>
        <section class="pt-32 pb-20 md:pt-40 md:pb-28 relative">
            <div class="hero-gradient absolute inset-0"></div>
            <div class="container mx-auto px-6 text-center relative">
                <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 leading-tight tracking-tighter">
                    Ubah Data Akademik <br class="hidden md:block" /> Menjadi <span class="text-indigo-600">Wawasan
                        Cerdas</span>
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-600">
                    Skolabs adalah LMS dengan Kecerdasan Buatan yang secara otomatis menganalisis performa siswa,
                    membantu Anda mengidentifikasi risiko, dan mengambil tindakan lebih awal.
                </p>
                <div class="mt-10 flex justify-center gap-4 flex-wrap">
                    <a href="/register"
                        class="bg-indigo-600 text-white px-8 py-3.5 rounded-lg font-semibold hover:bg-indigo-700 transition-all shadow-lg transform hover:scale-105">
                        Coba Analisis Otomatis
                    </a>
                    <a href="https://github.com/" target="_blank"
                        class="bg-white text-slate-700 px-8 py-3.5 rounded-lg font-semibold hover:bg-slate-100 transition-all border border-slate-300 shadow-sm transform hover:scale-105">
                        Pelajari Kodenya
                    </a>
                </div>

                <div class="mt-16 max-w-5xl mx-auto">
                    <img src="https://placehold.co/1200x600/E0E7FF/4338CA?text=Dashboard+Analisis+AI+Skolabs"
                        alt="Dashboard Analisis Akademik Cerdas Skolabs"
                        class="rounded-xl shadow-2xl ring-1 ring-slate-900/10">
                </div>
            </div>
        </section>

        <section id="fitur" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Fitur Inti untuk Pendidik Modern</h2>
                    <p class="mt-4 max-w-xl mx-auto text-slate-600">Kami mengubah cara Anda memahami data siswa, dari
                        proses manual menjadi otomatis dan cerdas.</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        class="bg-slate-50 p-8 rounded-xl border border-slate-200 hover:shadow-lg hover:border-indigo-200 transition-all">
                        <div
                            class="bg-indigo-100 text-indigo-600 rounded-full h-12 w-12 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                <path
                                    d="M12 2v2M8.46 3.51 7.05 4.93M3.51 8.46 4.93 7.05M21.99 12.01h-2M15.54 20.49l1.41-1.41M20.49 15.54l-1.41 1.41M3.51 15.54l1.41-1.41M8.46 20.49l-1.41-1.41" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Analisis Akademik Otomatis</h3>
                        <p class="mt-2 text-slate-600">AI kami menganalisis nilai untuk menemukan pola dan memprediksi
                            siswa yang butuh perhatian khusus.</p>
                    </div>
                    <div
                        class="bg-slate-50 p-8 rounded-xl border border-slate-200 hover:shadow-lg hover:border-indigo-200 transition-all">
                        <div
                            class="bg-indigo-100 text-indigo-600 rounded-full h-12 w-12 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 20v-6M6 20v-4M18 20v-8"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Dasbor Visual Interaktif</h3>
                        <p class="mt-2 text-slate-600">Sajikan data kompleks dalam grafik (Chart.js) yang mudah dipahami
                            untuk melacak kemajuan kelas.</p>
                    </div>
                    <div
                        class="bg-slate-50 p-8 rounded-xl border border-slate-200 hover:shadow-lg hover:border-indigo-200 transition-all">
                        <div
                            class="bg-indigo-100 text-indigo-600 rounded-full h-12 w-12 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Manajemen Kelas Terpusat</h3>
                        <p class="mt-2 text-slate-600">Kelola materi, tugas, dan penilaian siswa dalam satu platform
                            terpadu yang efisien dan rapi.</p>
                    </div>
                    <div
                        class="bg-slate-50 p-8 rounded-xl border border-slate-200 hover:shadow-lg hover:border-indigo-200 transition-all">
                        <div
                            class="bg-indigo-100 text-indigo-600 rounded-full h-12 w-12 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900">Gratis & Open-Source</h3>
                        <p class="mt-2 text-slate-600">Dapat digunakan selamanya tanpa biaya. Kode sumber terbuka untuk
                            dikembangkan bersama oleh komunitas.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="testimoni" class="py-20 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Dipercaya oleh Para Pendidik Inovatif
                    </h2>
                    <p class="mt-4 max-w-xl mx-auto text-slate-600">Lihat bagaimana Skolabs membantu mereka mengambil
                        keputusan berbasis data.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white p-8 rounded-xl shadow-md border border-slate-200">
                        <p class="text-slate-600 mb-6">"Analisis otomatis dari Skolabs sangat transformatif. Guru kami
                            tidak lagi menghabiskan waktu berjam-jam menganalisis spreadsheet dan bisa langsung fokus
                            membantu siswa yang diidentifikasi oleh AI."</p>
                        <div class="flex items-center">
                            <img src="https://placehold.co/48x48/E0E7FF/4338CA?text=SA" alt="Foto Ibu Siti Aminah"
                                class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold text-slate-900">Siti Aminah, S.Pd.</p>
                                <p class="text-sm text-slate-500">Kepala Sekolah, SMAN 1 Harapan Bangsa</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-xl shadow-md border border-slate-200">
                        <p class="text-slate-600 mb-6">"Fitur dasbornya luar biasa. Saya bisa melihat tren performa
                            seluruh kelas dalam hitungan detik, bukan jam. Ini benar-benar mengubah cara saya
                            merencanakan strategi pengajaran dan intervensi."</p>
                        <div class="flex items-center">
                            <img src="https://placehold.co/48x48/E0E7FF/4338CA?text=AF" alt="Foto Bapak Ahmad Fauzi"
                                class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <p class="font-semibold text-slate-900">Ahmad Fauzi, M.Kom.</p>
                                <p class="text-sm text-slate-500">Guru TIK, SMP Negeri 2 Cerdas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tentang" class="py-20 bg-white">
            <div class="container mx-auto px-6 max-w-4xl">
                <div class="text-center">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Berawal dari Sebuah Skripsi</h2>
                    <p class="mt-4 text-lg text-slate-600">
                        Skolabs lahir dari penelitian untuk menjawab tantangan utama pendidik: bagaimana mengubah data
                        akademik menjadi wawasan yang dapat ditindaklanjuti secara efisien? Dengan mengintegrasikan <b
                            class="text-indigo-600">Kecerdasan Buatan</b>, proyek ini bertujuan untuk menyediakan alat
                        bantu analisis yang canggih, gratis, dan terbuka bagi seluruh institusi pendidikan.
                    </p>
                    <div class="mt-8">
                        <a href="https://github.com/" target="_blank"
                            class="bg-slate-800 text-white px-8 py-3.5 rounded-lg font-semibold hover:bg-slate-900 transition-all shadow-lg transform hover:scale-105">
                            Mari Berkontribusi di GitHub
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-indigo-600 text-white">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-bold">Siap Mengajar dengan Data?</h2>
                <p class="mt-4 max-w-xl mx-auto text-indigo-200">Tinggalkan analisis manual. Biarkan AI membantu Anda
                    fokus pada hal yang paling penting: keberhasilan siswa.</p>
                <div class="mt-8">
                    <a href="/register"
                        class="bg-white text-indigo-600 px-8 py-3.5 rounded-lg font-semibold hover:bg-slate-100 transition-all shadow-lg transform hover:scale-105">
                        Dapatkan Wawasan Cerdas, Gratis!
                    </a>
                </div>
            </div>
        </section>

    </main>

    <footer class="bg-slate-800 text-slate-400">
        <div class="container mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center">
            <p>&copy; 2025 Skolabs. Proyek Open-Source untuk Pendidikan.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="https://github.com/" target="_blank" class="hover:text-white transition-colors"
                    aria-label="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="h-6 w-6">
                        <path
                            d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                        </path>
                    </svg>
                </a>
                <a href="#" class="hover:text-white transition-colors" aria-label="LinkedIn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="h-6 w-6">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                        <rect x="2" y="9" width="4" height="12"></rect>
                        <circle cx="4" cy="4" r="2"></circle>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

</body>

</html>
