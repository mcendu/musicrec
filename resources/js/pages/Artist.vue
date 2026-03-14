<script setup lang="ts">
import type { Track } from '@/types/track';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: number;
    name: string;
    tracks: Track[];
    navigation: {
        limit: number;
        next?: string;
        prev?: string;
    };
}>();
</script>

<template>
    <article>
        <h1>Arctic Monkeys</h1>
        <h2>Tracks</h2>
        <ul>
            <li v-for="track in tracks" :key="track.id">
                <Link :href="`/tracks/${track.id}`">{{ track.name }}</Link>
            </li>
        </ul>
    </article>
</template>
