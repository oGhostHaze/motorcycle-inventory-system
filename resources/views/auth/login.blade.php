<x-guest-layout>
    {{-- Modern Login Container with Gradient Background --}}
    <div
        class="flex items-center justify-center min-h-screen px-4 bg-gradient-to-br from-blue-50 via-white to-indigo-50 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            {{-- Logo and Header Section --}}
            <div class="text-center">
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto shadow-lg bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl">
                    <x-authentication-card-logo class="w-8 h-8 text-white" />
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Welcome Back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Sign in to your inventory management account
                </p>
            </div>

            {{-- Main Login Card --}}
            <div class="p-8 border shadow-xl bg-white/80 backdrop-blur-sm rounded-2xl border-white/20">
                {{-- Validation Errors --}}
                <x-validation-errors class="mb-6" />

                {{-- Status Message --}}
                @session('status')
                    <div class="p-4 mb-6 border-l-4 border-green-400 rounded-r-lg bg-green-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ $value }}</p>
                            </div>
                        </div>
                    </div>
                @endsession

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Email Field --}}
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="username" required
                                autofocus value="{{ old('email') }}"
                                class="block w-full py-3 pl-10 pr-3 placeholder-gray-400 transition-all duration-200 border border-gray-300 shadow-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your email address">
                        </div>
                    </div>

                    {{-- Password Field --}}
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                required
                                class="block w-full py-3 pl-10 pr-3 placeholder-gray-400 transition-all duration-200 border border-gray-300 shadow-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter your password">
                        </div>
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox"
                                class="w-4 h-4 text-blue-600 transition-colors duration-200 border-gray-300 rounded focus:ring-blue-500">
                            <label for="remember_me" class="block ml-2 text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-sm">
                                <a href="{{ route('password.request') }}"
                                    class="font-medium text-blue-600 transition-colors duration-200 hover:text-blue-500">
                                    Forgot password?
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Login Button --}}
                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="w-5 h-5 text-blue-300 transition-colors duration-200 group-hover:text-blue-200"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            Sign In
                        </button>
                    </div>
                </form>

                {{-- Additional Info --}}
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        Secure inventory management system
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    {{-- Custom Styles for Enhanced Aesthetics --}}
    <style>
        /* Custom animations and improvements */
        .bg-gradient-to-br {
            background-image: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #eef2ff 100%);
        }

        /* Floating animation for the logo */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .bg-gradient-to-r {
            animation: float 6s ease-in-out infinite;
        }

        /* Form field focus effects */
        .focus\:ring-2:focus {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        /* Button hover effects */
        .group:hover .group-hover\:text-blue-200 {
            color: #bfdbfe;
        }

        /* Backdrop blur fallback */
        @supports not (backdrop-filter: blur(12px)) {
            .backdrop-blur-sm {
                background-color: rgba(255, 255, 255, 0.95);
            }
        }

        /* Enhanced card shadow */
        .shadow-xl {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Smooth transitions for all interactive elements */
        input,
        button,
        a {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</x-guest-layout>
