<?= "<?php" ?>

namespace Tests\Feature\Http{{ str_replace('/', '\\', $temp['classpath']) }};

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;


class {{ $temp['name'] }} extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Laravel');
        });
    }
}

