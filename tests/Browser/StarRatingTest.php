<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StarRatingTest extends DuskTestCase
{
    public function test_star_rating_initial_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->assertPresent('.star-rating')
                ->assertSeeIn('#ratingText', 'Tap a star to rate');
        });
    }

    public function test_star_rating_shows_hover_text(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->mouseover('.star-label[data-value="3"]')
                ->assertSeeIn('#ratingText', 'Rate: 3 / 5');
        });
    }

    public function test_star_rating_highlights_on_hover(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->mouseover('.star-label[data-value="4"]');

            $activeStars = $browser->script(
                "return document.querySelectorAll('.star-label.active').length;"
            )[0];

            $this->assertEquals(4, $activeStars, 'Hovering star 4 should highlight 4 stars');

            $browser->assertSeeIn('#ratingText', 'Rate: 4 / 5');
        });
    }

    public function test_star_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->click('.star-label[data-value="4"]')
                ->assertSeeIn('#ratingText', 'Your rating: 4 / 5');

            $activeStars = $browser->script(
                "return document.querySelectorAll('.star-label.active').length;"
            )[0];

            $this->assertEquals(4, $activeStars, 'After clicking star 4, exactly 4 stars should be active');
        });
    }

    public function test_star_selection_persists_after_mouseout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->click('.star-label[data-value="2"]')
                ->mouseover('.star-rating')
                ->mouseover('body');

            $activeStars = $browser->script(
                "return document.querySelectorAll('.star-label.active').length;"
            )[0];

            $this->assertEquals(2, $activeStars, 'Star 2 selection should persist after mouse movement');
            $browser->assertSeeIn('#ratingText', 'Your rating: 2 / 5');
        });
    }

    public function test_star_re_selection_changes_rating(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->click('.star-label[data-value="5"]')
                ->assertSeeIn('#ratingText', 'Your rating: 5 / 5')
                ->click('.star-label[data-value="3"]')
                ->assertSeeIn('#ratingText', 'Your rating: 3 / 5');

            $activeStars = $browser->script(
                "return document.querySelectorAll('.star-label.active').length;"
            )[0];

            $this->assertEquals(3, $activeStars, 'After re-selecting star 3, exactly 3 stars should be active');
        });
    }

    public function test_hidden_radio_updates_on_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/testimonials')
                ->click('.star-label[data-value="1"]');

            $checkedValue = $browser->script(
                "return document.querySelector('.star-input:checked')?.value || null;"
            )[0];

            $this->assertEquals('1', $checkedValue, 'Hidden radio with value 1 should be checked');
        });
    }
}
