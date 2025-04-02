CREATE TABLE recipes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    prep_time VARCHAR(50),
    difficulty INTEGER CHECK (difficulty >= 1 AND difficulty <= 3),
    vegetarian BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE ratings (
    id SERIAL PRIMARY KEY,
    recipe_id INTEGER NOT NULL REFERENCES recipes(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5)
);