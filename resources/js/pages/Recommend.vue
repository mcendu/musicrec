<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { ArtistReference } from '@/types/artist';
import type { TrackReference } from '@/types/track';

interface TrackUrl {
    website: string;
    url: string;
}

defineProps<{
    track?: {
        id: number;
        name: string;
        artist: ArtistReference;
        urls: TrackUrl[];
    };
    recommendations: TrackReference[];
}>();
</script>

<template>
    <article class="my-4 max-w-xl">
        <template v-if="track">
            <header>
                <h1>{{ track.name }}</h1>
                <small>
                    By
                    <Link class="text-blue-600" :href="`/artists/${track.artist.id}`">{{
                        track.artist.name
                    }}</Link>
                </small>
            </header>
            <h2>Listen</h2>
            <ul>
                <li v-for="url in track.urls" :key="url.website">
                    <a :href="url.url">{{ url.website }}</a>
                </li>
            </ul>
        </template>
        <h2 v-if="track">You may also like</h2>
        <template v-else>
            <h1>Welcome to musicrec</h1>
            <h2>Random tracks for you</h2>
        </template>
        <table>
            <tr v-for="t in recommendations" :key="t.id">
                <td>
                    <Link class="text-blue-600" :href="`/tracks/${t.id}`">
                        {{ t.name }}
                    </Link>
                </td>
                <td>
                    <Link
                        class="text-blue-600"
                        v-if="t.artist"
                        :href="`/artists/${t.artist.id}`"
                    >
                        {{ t.artist.name }}
                    </Link>
                </td>
            </tr>
        </table>
    </article>
</template>
