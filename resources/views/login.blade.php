<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Attendia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        .bg-teal-dark { background-color: #134B46; }
        .text-teal-dark { color: #134B46; }
        /* Memastikan tidak ada scroll pada layar kecil */
        @media (max-height: 700px) {
            .main-card { transform: scale(0.9); }
        }
    </style>
</head>
<body class="bg-gray-50 h-screen w-screen flex items-center justify-center p-4 md:p-0">

    <div class="main-card max-w-5xl w-full bg-white rounded-[30px] md:rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row h-full max-h-[90vh] md:h-auto md:min-h-[550px]">
        
        <!-- Sisi Kiri: Ilustrasi (Disembunyikan di Mobile Kecil agar tidak scroll) -->
        <div class="hidden md:flex md:w-1/2 bg-teal-dark p-8 text-white flex-col justify-between items-center text-center">
            <div class="w-full flex justify-start">
                <span class="text-xl font-bold tracking-tighter">Attendia.</span>
            </div>
            
            <div class="flex flex-col items-center">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/login-page-illustration-download-in-svg-png-gif-file-formats--user-interface-access-security-protection-pack-network-communication-illustrations-6113110.png" 
                     alt="Login Illustration" class="w-48 lg:w-64 mb-6 drop-shadow-2xl">
                <h2 class="text-2xl font-bold mb-2 text-orange-300 uppercase tracking-wide">Sistem Absensi</h2>
                <p class="text-teal-100/70 text-sm leading-relaxed px-4">Manajemen kehadiran guru & siswa lebih akurat dan efisien.</p>
            </div>

            <div class="text-xs text-teal-200/40">
                &copy; {{ date('Y') }} Attendia Team
            </div>
        </div>

        <!-- Sisi Kanan: Form Login -->
        <div class="w-full md:w-1/2 p-8 md:p-12 lg:p-16 flex flex-col justify-center overflow-y-auto">
            <!-- Logo Mobile Only -->
            <div class="md:hidden flex justify-center mb-6">
                <span class="text-2xl font-bold tracking-tighter text-teal-dark border-b-4 border-orange-300">Attendia.</span>
            </div>

            <div class="mb-8 text-center md:text-left">
                <h3 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Selamat Datang</h3>
                <p class="text-gray-400 text-sm">Silahkan masuk ke akun administrator Anda.</p>
            </div>

            <!-- Alert Error (Dibuat lebih ringkas) -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-700 text-[11px] md:text-xs font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 ml-1 uppercase">Nomor Serial / NIP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <input type="text" name="serial_number" placeholder="Masukkan NIP" required
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-teal-700/10 focus:border-teal-dark transition outline-none text-sm font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5 ml-1 uppercase">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                               class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-teal-700/10 focus:border-teal-dark transition outline-none text-sm font-medium">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-teal-dark">
                            <i id="eye-icon" class="bi bi-eye-slash text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            class="w-full bg-teal-dark hover:bg-opacity-95 text-white font-bold py-4 rounded-2xl shadow-lg shadow-teal-900/20 transition transform active:scale-[0.98] uppercase tracking-[0.15em] text-xs">
                        MASUK SEKARANG
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-[11px] text-gray-400">Kesulitan akses? <a href="#" class="text-teal-dark font-bold hover:underline">Hubungi IT Support</a></p>
            </div>
        </div>
    </div>

    <!-- Script Mata Password -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }
    </script>
</body>
</html>