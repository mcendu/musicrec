<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { TrackReference } from '@/types/track';

defineProps<{
    id: number;
    name: string;
    tracks: TrackReference[];
    navigation: {
        limit: number;
        next?: string;
        prev?: string;
    };
}>();
</script>

<template>
    <article class="mx-auto my-4 max-w-2xl">
        <h1 class="text-3xl font-bold">{{ name }}</h1>
        <h2 class="text-2xl mt-3">Tracks</h2>
        <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
            <li v-for="track in tracks" :key="track.id" class="py-1">
                <Link class="text-blue-600" :href="`/tracks/${track.id}`">
                    {{ track.name }}
                </Link>
            </li>
        </ul>
        <nav>
            <Link
                v-if="navigation.prev"
                :href="`/artists/${id}?limit=${navigation.limit}&cursor=${navigation.prev}`"
            >
                Previous
            </Link>
            <Link
                v-if="navigation.next"
                :href="`/artists/${id}?limit=${navigation.limit}&cursor=${navigation.next}`"
            >
                Next
            </Link>
        </nav>
    </article>
</template>
