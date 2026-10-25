<?php

namespace App\Support;

class DemoCatalog
{
    public static function vendorGroups(): array
    {
        return [
            'food' => [
                [
                    'name' => 'KFC',
                    'description' => 'Crispy fried chicken, sandwiches, wings, and family meals prepared for quick pickup or delivery.',
                    'products' => [
                        ['name' => 'Fried Chicken', 'description' => 'Crispy bone-in chicken served hot with a seasoned coating and classic sides.'],
                        ['name' => 'Chicken Burger', 'description' => 'A crunchy chicken fillet with lettuce, sauce, and a soft toasted bun.'],
                        ['name' => 'Chicken Wings', 'description' => 'Tender wings tossed in a savory glaze, ideal for sharing.'],
                        ['name' => 'Chicken Nuggets', 'description' => 'Bite-sized chicken pieces with a crisp coating and dipping sauce.'],
                    ],
                ],
                [
                    'name' => 'McDonalds',
                    'description' => 'Fast burgers, chicken sandwiches, fries, and familiar meals for everyday cravings.',
                    'products' => [
                        ['name' => 'Big Mac', 'description' => 'Two beef patties layered with lettuce, cheese, pickles, onions, and special sauce.'],
                        ['name' => 'Cheeseburger', 'description' => 'A simple grilled beef burger with cheese, pickles, onions, ketchup, and mustard.'],
                        ['name' => 'McChicken', 'description' => 'Crispy chicken with shredded lettuce and mayo on a toasted bun.'],
                        ['name' => 'Filet-O-Fish', 'description' => 'Breaded fish fillet with tartar sauce and cheese on a steamed bun.'],
                    ],
                ],
                [
                    'name' => 'Burger King',
                    'description' => 'Flame-grilled burgers, chicken, fries, and filling combo meals made for delivery.',
                    'products' => [
                        ['name' => 'Whopper', 'description' => 'A flame-grilled beef burger with tomatoes, lettuce, mayo, ketchup, pickles, and onions.'],
                        ['name' => 'Bacon King', 'description' => 'A double beef burger stacked with bacon, cheese, ketchup, and mayo.'],
                        ['name' => 'Double Whopper', 'description' => 'Two flame-grilled patties with fresh toppings on a sesame seed bun.'],
                        ['name' => 'Bacon Double Cheeseburger', 'description' => 'Two beef patties with melted cheese, smoky bacon, pickles, and sauce.'],
                    ],
                ],
                [
                    'name' => 'Subway',
                    'description' => 'Made-to-order sandwiches, wraps, and salads with fresh toppings and sauces.',
                    'products' => [
                        ['name' => 'Italian B.M.T.', 'description' => 'A deli-style sub with salami, pepperoni, ham, cheese, vegetables, and sauce.'],
                        ['name' => 'Meatball Marinara', 'description' => 'Warm meatballs in marinara sauce with melted cheese on fresh bread.'],
                        ['name' => 'Spicy Italian', 'description' => 'Pepperoni and salami with vegetables, cheese, and your choice of sauce.'],
                        ['name' => 'Chicken & Bacon Ranch Melt', 'description' => 'Chicken, bacon, melted cheese, and ranch dressing on toasted bread.'],
                    ],
                ],
            ],
            'grocery' => [
                [
                    'name' => 'Walmart',
                    'description' => 'Everyday groceries, pantry staples, fresh produce, and household essentials in one basket.',
                    'products' => [
                        ['name' => 'Milk', 'description' => 'Fresh dairy milk for breakfast, cooking, coffee, and everyday use.'],
                        ['name' => 'Carrot', 'description' => 'Crunchy fresh carrots for salads, stews, roasting, or snacking.'],
                        ['name' => 'Apple', 'description' => 'Crisp apples selected for fresh eating, lunch boxes, and baking.'],
                        ['name' => 'Eggs', 'description' => 'Fresh eggs packed for breakfast, baking, and quick family meals.'],
                        ['name' => 'Bread', 'description' => 'Soft sliced bread for toast, sandwiches, and table service.'],
                    ],
                ],
                [
                    'name' => 'Target',
                    'description' => 'Fresh groceries, proteins, pantry items, and household favorites for convenient weekly shopping.',
                    'products' => [
                        ['name' => 'Fish', 'description' => 'Fresh fish portions ready for grilling, baking, or pan-searing.'],
                        ['name' => 'Chicken Wings', 'description' => 'Fresh chicken wings ready for roasting, frying, or saucing.'],
                        ['name' => 'Beef', 'description' => 'Quality beef cuts for stews, grilling, stir-fries, and family dinners.'],
                        ['name' => 'Pork', 'description' => 'Fresh pork cuts suited for roasting, braising, and weeknight meals.'],
                        ['name' => 'Lamb', 'description' => 'Tender lamb portions for stews, grills, and special meals.'],
                    ],
                ],
            ],
            'commerce' => [
                [
                    'name' => 'BloomBask',
                    'description' => 'Sustainable home goods and reusable essentials for shoppers who prefer practical, low-waste products.',
                    'products' => [
                        ['name' => 'Reusable Bamboo Kitchen Towels', 'description' => 'Washable bamboo towels that replace paper towels for everyday cleanup.'],
                        ['name' => 'Soy Wax Scented Candle - Lavender Fields', 'description' => 'A clean-burning lavender candle made with soy wax for relaxed evenings.'],
                        ['name' => 'Organic Cotton Mesh Grocery Bags', 'description' => 'Reusable cotton bags for produce, bulk items, and market runs.'],
                        ['name' => 'Glass Water Bottle with Silicone Sleeve', 'description' => 'A reusable glass bottle with a protective sleeve for daily hydration.'],
                        ['name' => 'Mini Herb Grow Kit (Basil & Mint)', 'description' => 'A compact grow kit for fresh basil and mint on a kitchen counter.'],
                    ],
                ],
                [
                    'name' => 'HypeLane',
                    'description' => 'Streetwear, sneaker care, and limited-style accessories curated for fashion-forward shoppers.',
                    'products' => [
                        ['name' => 'Yeezy Boost 350 V2 - Onyx', 'description' => 'A sleek black knit sneaker with responsive cushioning and a low-profile shape.'],
                        ['name' => 'Supreme Box Logo Cap - Black', 'description' => 'A black streetwear cap with a structured fit and embroidered front logo.'],
                        ['name' => 'Off-White Oversized Denim Jacket', 'description' => 'An oversized denim jacket with a relaxed cut and statement streetwear finish.'],
                        ['name' => 'Nike Air Jordan 1 Retro High OG - UNC', 'description' => 'A high-top sneaker in classic blue and white with heritage basketball styling.'],
                        ['name' => 'HypeLane Limited Sneaker Storage Box', 'description' => 'A stackable display box that helps keep sneakers organized and protected.'],
                    ],
                ],
            ],
            'pharmacy' => [
                [
                    'name' => 'CVS',
                    'description' => 'Pharmacy essentials, personal care, wellness items, and everyday health products delivered conveniently.',
                    'products' => [
                        ['name' => 'Mouthwash', 'description' => 'Daily oral rinse for fresher breath and a cleaner mouth after brushing.'],
                        ['name' => 'Colgate Toothbrush', 'description' => 'A soft-bristle toothbrush designed for comfortable daily oral care.'],
                        ['name' => 'Durex Condom', 'description' => 'Individually sealed condoms for personal protection and sexual wellness.'],
                        ['name' => 'Vitamin C', 'description' => 'Vitamin C tablets for daily wellness support as part of a balanced routine.'],
                    ],
                ],
            ],
            'service' => [
                [
                    'name' => 'ByteMedic',
                    'description' => 'Practical computer repair, malware cleanup, diagnostics, and recovery support for home and office devices.',
                    'services' => [
                        ['name' => 'Diagnose & Software Checkup', 'description' => 'A full device inspection covering performance, software health, and common faults.'],
                        ['name' => 'Virus & Spyware Removal', 'description' => 'Malware cleanup, security checks, and basic protection setup for infected devices.'],
                        ['name' => 'Data Recovery', 'description' => 'Recovery support for deleted files, failing drives, and inaccessible storage.'],
                    ],
                ],
                [
                    'name' => 'AutoRevive Garage',
                    'description' => 'Reliable vehicle maintenance for routine servicing, brake work, and quick mechanical checks.',
                    'services' => [
                        ['name' => 'Engine & Oil, Filter Change', 'description' => 'Oil and filter replacement with a basic engine bay inspection.'],
                        ['name' => 'Brake Pads & Rotors Replacement', 'description' => 'Brake pad and rotor replacement for safer, smoother stopping.'],
                    ],
                ],
                [
                    'name' => 'Glow Grace Studio',
                    'description' => 'Beauty and wellness appointments for bridal makeup, event styling, and relaxation sessions.',
                    'services' => [
                        ['name' => 'Bridal Makeover', 'description' => 'A polished bridal makeup session tailored for photos, ceremonies, and receptions.'],
                        ['name' => 'Massage Therapy', 'description' => 'A relaxing massage session focused on comfort, tension relief, and recovery.'],
                    ],
                ],
                [
                    'name' => 'HandyHive',
                    'description' => 'Trusted home maintenance help for repairs, assembly, painting, cleaning, and small improvement jobs.',
                    'services' => [
                        ['name' => 'Plumbing', 'description' => 'Help with leaks, fittings, blocked drains, and small plumbing repairs.'],
                        ['name' => 'Electrical Repair', 'description' => 'Basic electrical troubleshooting, fixture repairs, and safe minor installations.'],
                        ['name' => 'Carpentry', 'description' => 'Small carpentry repairs, trim work, shelving, and practical woodwork tasks.'],
                        ['name' => 'Painting', 'description' => 'Interior painting for rooms, touch-ups, walls, doors, and trims.'],
                        ['name' => 'Furniture Assembly', 'description' => 'Assembly support for flat-pack furniture, shelves, tables, and storage units.'],
                        ['name' => 'Home Cleaning', 'description' => 'General home cleaning for kitchens, bathrooms, living areas, and bedrooms.'],
                    ],
                ],
                [
                    'name' => 'Plug & Play Installations',
                    'description' => 'Clean, professional setup for home entertainment, cooling, connectivity, and security equipment.',
                    'services' => [
                        ['name' => 'Cable TV Installation', 'description' => 'Cable TV setup with tidy wiring and basic signal checks.'],
                        ['name' => 'Air Conditioner Installation', 'description' => 'Air conditioner installation with placement, mounting, and startup checks.'],
                        ['name' => 'Home Theater Installation', 'description' => 'TV, speaker, and media setup for a cleaner home entertainment experience.'],
                        ['name' => 'Home Security System Installation', 'description' => 'Camera, sensor, and security device setup for safer home monitoring.'],
                    ],
                ],
            ],
        ];
    }

    public static function vendorsFor(string $slug): array
    {
        return self::vendorGroups()[$slug] ?? [];
    }
}
