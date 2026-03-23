# musicrec: Track recommender
This project implements a privacy focused song recommendation system. The
system, based on the track the user is browsing, recommends several more
tracks to the user.

## Background

## Implementation

### Tech stack
This application is built on Laravel and Vue. Laravel is a
model-view-controller web framework built using PHP, a classic programming
language known for its widespread usage in web backends during the late
2000s. Vue is a modern frontend framework where developers build web pages
from self-contained components with self-contained layouts, styles and
scripts.

A related framework, Inertia, is used for quickly passing data between
the backend and frontend. In a normal single-page application, the frontend
runs web API queries with XMLHttpRequest or Fetch, and renders "pages"
based on whatever the backend returns. Inertia abstracts away the process:
it collects data sent by the server and present them as props for use with
a root React, Vue, name-your-framework component, and acts as a client-side
router that automatically makes requests whenever the user tries to navigate
to another page of the same web app. The frontend developer using Inertia
no longer need to deal with writing templates in backend-specific template
languages they are unfamiliar with, so that I can care less about backend
stuff when dealing with the frontend.

### Database
The database schema resembles that of a common music web app:

- The `artists` table consists of info on an artist, someone who sings a
  song or produces a track. The contents are currently minimal; it only
  consists of a name field.
- The `tracks` table contains basic info about a track, including its
  title and artist. Unique to this application is a `tags` field, a fixed
  length vector field used for recommending tracks; pgvector, a PostgreSQL
  extension that adds native vector types and indexing of vector columns,
  is used to speed up the recommendation process.
- The `urls` table contains links to other websites, where the user can
  listen to the actual track. It forms a many-to-one relation with the
  `track` table.

musicrec is designed with PostgreSQL in mind, and uses many facilities
specific to it. The migrations are able to run from start to finish after
a blank database is set up with the proper Postgres extensions:

```sql
CREATE EXTENSION IF NOT EXISTS vector;
CREATE EXTENSION IF NOT EXISTS tsm_system_rows;
```

### Sourcing and transforming data
A music recommendation system is required to have data for music to start
with. For this project, data is sourced from Last.fm, where the metadata
and tags for the most popular tracks of a select few genres are collected
through their API.

The raw data from Last.fm is stored in a database separate from that of
musicrec's. There are notable differences between the two schemas: tags
are stored as a separate table instead of as a column, and the tags
associated with each track is stored in yet another table, to form a
many-to-many relationship between tracks and tags.

The raw tags has to be transformed before it can be used with musicrec's
recommendation system. 128 tags are selected for use. the tags in the
collected data are sorted in order of significance, and the first 128 tags
on the list, excluding tags pertaining to artists and as determined by
other subjective factors, are assigned elements in musicrec's `tags`
vector.

### The recommendation algorithm
The recommendation scheme is based on the `tags` column in the tracks
table as mentioned above. The recommender takes a 128-element vector as
input, and outputs the tracks closest to it based on cosine similarity.

A database index is built over the `tags` column to optimize queries.
While the default B-tree works for simple data types like numbers and
strings, it does not allow efficient retrieval of similar vectors, so
alternative index data structures are required. This project uses the
Hierarchical navigable small world (HNSW) algorithm (Malkov, Yashunin,
2020), natively supported by the pgvector extension, to index over the
vector column, allowing efficient generation of recommendations.

### Deployment
The app is tentatively deployed at
<https://musicrec-main-vji91a.free.laravel.cloud/>. Laravel Cloud,
apparantly built on AWS, is used to speed up the deployment process.
The source code is available at <https://github.com/mcendu/musicrec/>.

## Evaluation
As of time of writing, the project is not fully complete, with lack of
motivation being a key factor that hampers progress.

Some issues with the current approach was found during development.
A quick analysis of the Last.fm tag data revealed significant flaws
that can hamper the effect of recommendation. On Last.fm, users apply
tags to songs, and less popular songs can have no tags applied to them
at all, rendering the recommendation process ineffective for them. A
potential point of improvement is to let a neural network assign genres
to tracks based on spectrograms of tracks.

On the frontend, styles are applied through usage of Tailwind classes;
the Laravel template project comes with Tailwind pulled in by default.
While Tailwind allows me to quickly put together basic page styles, in
the long run pages can be more difficult to maintain than CSS
stylesheets dedicated to specific components. 

As recommended by best practices in development, the backend has unit
tests covering API behavior. The APIs for retrieving basic info about
artists and tracks are fully covered. Due to discrepancies between
PostgreSQL (for which this application is written) and SQLite (which
Laravel uses for tests), the entire recommendation API is not covered
by tests, and some other tests fail.

## References
