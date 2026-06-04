<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MATCHGO — Find Your Futsal Rival' }}</title>
    <meta name="description" content="Platform matchmaking futsal — temukan lawan bermain, tentukan lapangan, dan bagi biaya secara transparan.">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN (for prototyping — replace with compiled CSS in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'heading': ['Fredoka', 'sans-serif'],
                        'body': ['Nunito', 'sans-serif'],
                    },
                    container: {
                        center: true,
                        padding: {
                            DEFAULT: '1rem',
                            sm: '2rem',
                            lg: '4rem',
                            xl: '5rem',
                            '2xl': '6rem',
                        },
                    },
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Nunito', sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(3deg); }
        }
        @keyframes pulse-soft {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        @keyframes dash {
            to { stroke-dashoffset: 0; }
        }

        /* SVG Doodle Draw Animation */
        .doodle-draw {
            stroke-dasharray: var(--dash-length, 500);
            stroke-dashoffset: var(--dash-length, 500);
            animation: dash var(--duration, 1s) cubic-bezier(0.65, 0, 0.35, 1) forwards;
            animation-delay: var(--delay, 0ms);
        }

        @keyframes bounce-gentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .animate-float { animation: float 4s ease-in-out infinite; }
        .animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
        .animate-pulse-soft { animation: pulse-soft 3s ease-in-out infinite; }
        .animate-bounce-gentle { animation: bounce-gentle 2s ease-in-out infinite; }

        .field-pattern {
            background-image:
                linear-gradient(rgba(76, 175, 80, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(76, 175, 80, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .grass-gradient {
            background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 50%, #66BB6A 100%);
        }
        .hero-glow {
            background: radial-gradient(ellipse at 50% 0%, rgba(129, 199, 132, 0.3) 0%, transparent 60%);
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased bg-[#F1F8E9] text-[#1B5E20]">

    @yield('content')

    <x-toaster />

    @stack('scripts')
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>