<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { ArtistReference } from '@/types/artist';
import type { TrackReference } from '@/types/track';

interface TrackUrl {
    website: string;
    url: string;
}

defineProps<{
    id: number;
    name: string;
    artist: ArtistReference;
    urls: TrackUrl[];
    recommendations: TrackReference[];
}>();
</script>

<template>
    <article class="my-4 max-w-xl">
        <header>
            <h1>{{ name }}</h1>
            <small>
                By
                <Link class="text-blue-600" :href="`/artists/${artist.id}`">{{
                    artist.name
                }}</Link>
            </small>
        </header>
        <h2>Listen</h2>
        <ul>
            <li v-for="url in urls" :key="url.website">
                <a :href="url.url">{{ url.website }}</a>
            </li>
        </ul>
        <h2>You may also like</h2>
        <table>
            <tr v-for="track in recommendations" :key="track.id">
                <td>
                    <Link class="text-blue-600" :href="`/tracks/${track.id}`">
                        {{ track.name }}
                    </Link>
                </td>
                <td>
                    <Link
                        class="text-blue-600"
                        v-if="track.artist"
                        :href="`/artists/${track.artist.id}`"
                    >
                        {{ track.artist.name }}
                    </Link>
                </td>
            </tr>
        </table>
    </article>
</template>
