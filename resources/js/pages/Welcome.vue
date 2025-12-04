<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const avatarUrl = 'https://avatars.githubusercontent.com/u/25482861?v=4';
</script>

<template>
    <Head title="Christopher Law">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen items-center justify-center bg-[#FDFDFC] px-4 py-12 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] sm:px-6 lg:px-8">
        <!-- Card Container -->
        <div class="w-full max-w-sm transform transition-all duration-300 hover:scale-[1.01]">
            <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-gray-900/5 dark:bg-[#161615] dark:ring-white/10 dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                
                <!-- Avatar & Header Section -->
                <div class="relative flex flex-col items-center p-8 pb-6 text-center">
                    <div class="relative mb-6 h-32 w-32 overflow-hidden rounded-full ring-4 ring-gray-50 shadow-md dark:ring-[#2C2C2A]">
                        <img 
                            :src="avatarUrl" 
                            alt="Christopher Law" 
                            class="h-full w-full object-cover transition-transform duration-500 hover:scale-110" 
                        />
                    </div>
                    
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Christopher Law</h1>
                    <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Software Developer</p>
                </div>

                <!-- Contact / Social Links -->
                <div class="space-y-3 px-8 pb-8">
                    <!-- Email -->
                    <a 
                        href="mailto:christopher-law@live.com" 
                        class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition-colors hover:border-gray-200 hover:bg-gray-100 dark:border-[#2C2C2A] dark:bg-[#1C1C1A] dark:hover:border-gray-700 dark:hover:bg-[#2C2C2A]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm dark:bg-[#2C2C2A] dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <span class="text-sm font-medium group-hover:text-gray-900 dark:group-hover:text-white">christopher-law@live.com</span>
                    </a>

                    <!-- X (Twitter) -->
                    <a 
                        href="https://x.com/ChristopherLaw_" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition-colors hover:border-gray-200 hover:bg-gray-100 dark:border-[#2C2C2A] dark:bg-[#1C1C1A] dark:hover:border-gray-700 dark:hover:bg-[#2C2C2A]"
                    >
                         <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm dark:bg-[#2C2C2A] dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>
                        </div>
                        <span class="text-sm font-medium group-hover:text-gray-900 dark:group-hover:text-white">@ChristopherLaw_</span>
                    </a>

                     <!-- LinkedIn -->
                    <a 
                        href="https://www.linkedin.com/in/christopher-t-law/" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition-colors hover:border-gray-200 hover:bg-gray-100 dark:border-[#2C2C2A] dark:bg-[#1C1C1A] dark:hover:border-gray-700 dark:hover:bg-[#2C2C2A]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm dark:bg-[#2C2C2A] dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </div>
                        <span class="text-sm font-medium group-hover:text-gray-900 dark:group-hover:text-white">LinkedIn</span>
                    </a>

                    <!-- GitHub -->
                    <a 
                        href="https://github.com/Christopher-Law" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition-colors hover:border-gray-200 hover:bg-gray-100 dark:border-[#2C2C2A] dark:bg-[#1C1C1A] dark:hover:border-gray-700 dark:hover:bg-[#2C2C2A]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm dark:bg-[#2C2C2A] dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        </div>
                        <span class="text-sm font-medium group-hover:text-gray-900 dark:group-hover:text-white">GitHub</span>
                    </a>
                </div>

                <!-- Action Button -->
                 <div class="border-t border-gray-100 bg-gray-50/50 p-6 dark:border-[#2C2C2A] dark:bg-[#1C1C1A]/50">
                    <Link
                        href="/cart"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#F53003] px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#D92902] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:ring-offset-2 dark:ring-offset-[#161615]"
                    >
                        <span>Go to App</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
