import type { ArtistReference } from "./artist";

export type TrackReference = {
    id: number,
    name: string,
    artist?: ArtistReference,
};
