<script setup lang="ts">
import type { ArtistReference } from '@/types/artist';
import { Link } from '@inertiajs/vue3';

interface TrackUrl {
    website: string;
    url: string;
}

defineProps<{
    id: number;
    name: string;
    artist: ArtistReference;
    urls: TrackUrl[];
}>();
</script>

<template>
    <article>
        <header>
            <h1>{{ name }}</h1>
            <small>
                By
                <Link :href="`/artists/${artist.id}`">{{ artist.name }}</Link>
            </small>
        </header>
        <h2>Listen</h2>
        <ul>
            <li v-for="url in urls" :key="url.website">
                <a :href="url.url">{{ url.website }}</a>
            </li>
        </ul>
    </article>
</template>
