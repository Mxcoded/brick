<?php

return [
    'name' => 'Website',

    /*
    |---------------------------------------------------------------------------
    | Role -> Website permission grants
    |---------------------------------------------------------------------------
    |
    | The only place in the module where a role name may appear. PHP reads this
    | through `config('website.roles')` and
    | `Modules\Website\Support\WebsitePermission::forRole()`; the taxonomy class
    | describes capabilities and never knows who holds them.
    |
    | Because the seeder revokes Website permissions from every role that is
    | absent from this table, listing a role is the only way it keeps access. A
    | role that does not appear here holds nothing.
    |
    | `*` expands to the whole taxonomy in `WebsitePermission::all()`. Anything
    | else is intersected with the taxonomy, so a typo drops a grant instead of
    | creating a permission nothing checks.
    |
    */

    'roles' => [

        // Administrators keep the whole module, including the capabilities the
        // website team does not get: deleting a booking, writing a testimonial
        // by hand.
        'admin' => ['*'],

        // Website team: runs the public site day to day. Incoming enquiries,
        // approving guest reviews, publishing rooms and amenities, campaigns and
        // subscribers.
        //
        // Deliberately absent:
        //   website.bookings.create    - a booking is created by a guest
        //                                  completing checkout, not typed in
        //   website.bookings.delete    - a booking is the ledger's receivable;
        //                                  removing it is finance's call
        //   website.testimonials.create - reviews are written by guests
        //
        // Note that `website.bookings.update` covers confirming, cancelling,
        // resending and assigning a room, because the legacy routes gate all of
        // those on the same capability.
        'website_admin' => [
            'website.bookings.read',
            'website.bookings.update',
            'website.testimonials.read',
            'website.testimonials.update',
            'website.testimonials.delete',
            'website.contact-messages.read',
            'website.contact-messages.update',
            'website.contact-messages.delete',
            'website.newsletter.read',
            'website.newsletter.create',
            'website.newsletter.update',
            'website.newsletter.delete',
            'website.subscribers.read',
            'website.subscribers.create',
            'website.subscribers.update',
            'website.subscribers.delete',
            'website.room-types.read',
            'website.room-types.create',
            'website.room-types.update',
            'website.room-types.delete',
            'website.amenities.read',
            'website.amenities.create',
            'website.amenities.update',
            'website.amenities.delete',
            'website.addons.read',
            'website.addons.create',
            'website.addons.update',
            'website.addons.delete',
            'website.dining.read',
            'website.dining.create',
            'website.dining.update',
            'website.dining.delete',
            'website.meeting.read',
            'website.meeting.create',
            'website.meeting.update',
            'website.meeting.delete',
            'website.facilities.read',
            'website.facilities.create',
            'website.facilities.update',
            'website.facilities.delete',
            'website.offers.read',
            'website.offers.create',
            'website.offers.update',
            'website.offers.delete',
            'website.settings.read',
            'website.settings.create',
            'website.settings.update',
            'website.settings.delete',
            'website.inventory.read',
            'website.inventory.create',
            'website.inventory.update',
            'website.inventory.delete',
        ],

        // `gm`, `ggm`, `finance` and everyone else are intentionally absent, so
        // the seeder leaves them without Website permissions.
        //
        // If senior management needs to read enquiries and bookings, add an
        // explicit read-only entry rather than reaching for `*`:
        //
        // 'gm' => [
        //     'website.bookings.read',
        //     'website.testimonials.read',
        //     'website.contact-messages.read',
        //     'website.subscribers.read',
        //     'website.newsletter.read',
        // ],
    ],

];
