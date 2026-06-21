// Demo-site content packs for the live previews. Each theme gets its own pack so
// its Playground demo reads like a real, specific site. `image` fields reference a
// cover by base name (see docs/demo/*.jpg); gen-gallery resolves them to URLs.
// DEMO_PACKS is filled per-theme; anything missing falls back to DEFAULT_PACK.

export const COVERS = ["ember","tide","grove","dusk","slate","sand"];

export const DEFAULT_PACK = {
  "site": {
    "name": "Northwind",
    "tagline": "Ideas worth shipping."
  },
  "author": {
    "name": "Mara Ellison",
    "bio": "Designer and engineer writing about craft, code, and calm."
  },
  "menuCategories": [
    "Design",
    "Engineering"
  ],
  "pages": [
    {
      "title": "About",
      "image": "slate",
      "content": "<p>Northwind is a small publication about craft, code, and calm — written for people who build things and care how they feel to use.</p><p>It is run by <strong>Mara Ellison</strong>, a designer and engineer who has spent a decade shipping software and learning, slowly, that less is usually more.</p><p>No trackers, no pop-ups, no newsletter to escape. Just writing, and the occasional good idea worth keeping.</p>"
    }
  ],
  "posts": [
    {
      "title": "Designing for calm",
      "category": "Design",
      "image": "dusk",
      "excerpt": "Good software feels quiet. Here is how to build interfaces that get out of the way.",
      "content": "<p>Good software feels quiet. It does the thing you asked, then steps out of the way and leaves room for your own thoughts.</p><h2>Less, but better</h2><p>Every element on a page is a small request for attention. The craft is deciding which requests are worth making.</p><blockquote>Simplicity is not the absence of detail. It is detail spent only where it counts.</blockquote><p>When in doubt, remove. The page that remains is almost always stronger.</p>"
    },
    {
      "title": "The real cost of a slow website",
      "category": "Engineering",
      "image": "tide",
      "excerpt": "Speed is a feeling before it is a number. Here is where the milliseconds hide.",
      "content": "<p>Speed is a feature you feel before you can name it. A page that loads instantly feels trustworthy; a slow one feels broken even when everything works.</p><h2>Where the milliseconds go</h2><ul><li>Web fonts that block the first paint</li><li>Hero images larger than their container</li><li>Scripts that run before anyone has scrolled</li></ul><p>Trim each one and the whole experience lightens.</p>"
    },
    {
      "title": "Notes on shipping small",
      "category": "Craft",
      "image": "ember",
      "excerpt": "The smallest version of an idea that still helps someone is the best place to start.",
      "content": "<p>The smallest version of an idea that still helps someone is usually the right place to start. You learn more from one real user than from a month of speculation.</p><h2>Ship, then listen</h2><p>Release early, watch closely, and let the next step reveal itself. Momentum compounds.</p>"
    },
    {
      "title": "A field guide to better mornings",
      "category": "Life",
      "image": "grove",
      "excerpt": "Protect the first hour. The rest of the day tends to follow it.",
      "content": "<p>The first hour sets the temperature for everything after it. Guard it like it matters, because it does.</p><h2>Three small rituals</h2><ul><li>Light before screens</li><li>One page of writing, badly, before the inbox</li><li>A walk short enough that you will actually take it</li></ul>"
    },
    {
      "title": "What old maps teach us about design",
      "category": "Ideas",
      "image": "sand",
      "excerpt": "The best maps leave things out on purpose. So should the best products.",
      "content": "<p>A map that showed everything would be useless — and exactly the size of the world. Every great map is an argument about what matters.</p><h2>The art of leaving out</h2><p>Designers are mapmakers. We choose a scale, pick a projection, and decide which roads earn a line. The omissions are the design.</p>"
    }
  ],
  "comments": [
    {
      "author": "Jonah Reed",
      "content": "This put words to something I have felt for years but could not name."
    },
    {
      "author": "Priya Nair",
      "content": "\"Detail spent only where it counts\" — stealing that for our review on Monday."
    },
    {
      "author": "Sam Okafor",
      "content": "Came for the typography, stayed for the philosophy. More of this, please."
    }
  ]
};

export const DEMO_PACKS = {
  "aurora": {
    "site": {
      "name": "The Long Way Round",
      "tagline": "Essays on reading, writing, and paying closer attention."
    },
    "author": {
      "name": "Marin Holloway",
      "bio": "Writer and lapsed librarian who keeps a slow journal about craft, books, and the art of noticing."
    },
    "menuCategories": [
      "Essays",
      "Notes",
      "Reading"
    ],
    "pages": [
      {
        "title": "About",
        "image": "sand",
        "content": "<p>I'm Marin. For nine years I shelved other people's books for a living, and somewhere in the quiet between requests I started keeping a notebook of my own. The Long Way Round is what spilled out of it: essays that take the scenic route, notes I scribble in margins, and the occasional argument with a book I love.</p><p>I write here about attention mostly. How hard it has become to give, how much it changes a day when you do, and how reading and writing are really just two disciplines for the same stubborn practice. I am not interested in productivity. I am interested in what stays with you after the lamp is off.</p><p>There's no schedule and no newsletter funnel. Things go up when they're ready. If something here makes you reach for a pen, or a book, or the window, then we've understood each other.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Hour Before the Day Asks Anything",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "On the small, unclaimed hour at dawn, and why I have stopped trying to make it useful.",
        "content": "<p>There is an hour at the start of the day when nothing has asked anything of me yet. The phone is still face-down. The kettle hasn't clicked. For a long time I tried to fill that hour with something improving, and for a long time it slipped through my fingers anyway.</p><p>What I've learned is that the hour does not want to be spent. It wants to be inhabited. I sit with a cup going cold and watch the light decide what color it's going to be, and somewhere in that watching the day arranges itself more kindly than it would have otherwise.</p><blockquote>The most productive thing I do all day is the thing that produces nothing.</blockquote><p>I don't recommend this as a system. Systems are how the morning gets colonized. I only mean that the unclaimed hour is worth protecting, and that protecting it costs less than you think and returns more than you'd believe.</p>"
      },
      {
        "title": "What I Underline, and Why",
        "category": "Reading",
        "image": "grove",
        "excerpt": "A small confession about the pencil marks I leave in the margins of everything I read.",
        "content": "<p>I am a margin-writer. Pristine books make me nervous; I never quite trust a person who finishes a novel and leaves it looking unread. The pencil is how I argue back, how I agree, how I mark the sentence that knocked the wind out of me so I can find it again at two in the morning.</p><p>Over the years a grammar has formed without my deciding it. Here is roughly what the marks mean:</p><ul><li>A single line: this is true, and I want to remember it's true.</li><li>A double line: this changed something, handle with care.</li><li>A question mark: I don't believe you yet, but I'm listening.</li><li>A star: come back here when you've forgotten why you write at all.</li></ul><p>Years later, the marks are a map of a former self. I open an old paperback and meet the person who underlined it, and we don't always agree. That disagreement is, I think, the whole point of keeping the books.</p>"
      },
      {
        "title": "On Finishing Badly",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "Why I have made peace with abandoning books, projects, and the version of me that thought quitting was failure.",
        "content": "<p>I used to finish everything. Every book I started, every dreadful film, every plan I'd announced too loudly to back out of. I mistook endurance for character. It took me an embarrassingly long time to notice that a thing finished out of stubbornness teaches you nothing except how to be stubborn.</p><p>Now I quit. Gently, and more often than I'll admit. A novel that doesn't earn its three hundredth page goes back on the shelf without ceremony. A sentence that won't come right after an afternoon gets left for the morning, or left for good.</p><p>The fear is that quitting is a slope, that one abandoned book leads to an abandoned life. I haven't found that to be true. What I've found is that letting go of the wrong thing is the only way I've ever made room for the right one.</p>"
      },
      {
        "title": "Notes From a Slow Week",
        "category": "Notes",
        "image": "sand",
        "excerpt": "Scraps from seven unremarkable days that turned out to be worth writing down.",
        "content": "<p>I keep a running page for the weeks that don't amount to an essay. Here is most of one, lightly tidied.</p><p>Monday: the bread didn't rise and I ate it anyway, dense as a doorstop, and it was somehow exactly right with butter. Tuesday: a stranger held a door and we both said sorry, which is the most honest greeting our country has. Wednesday: read forty pages standing up because sitting felt like giving in to the rain.</p><p>Thursday through Saturday blur, the way good weeks do. Sunday I wrote nothing and felt no guilt, which after years of practice is its own small achievement. None of this is news. That's the whole appeal. The week asked very little of me and I have decided, in return, to remember it.</p>"
      }
    ],
    "comments": [
      {
        "author": "Tessa Bramble",
        "content": "The unclaimed hour. I've been calling mine 'the no' and protecting it like a dragon, but yours is the better name. Thank you for putting words to it."
      },
      {
        "author": "Owen Carr",
        "content": "Read this on the train with my coffee going cold in exactly the way you describe. Felt seen, and slightly called out. Both welcome."
      },
      {
        "author": "Marin Holloway",
        "content": "Tessa, 'the no' is wonderful and I may steal it. Owen, the cold coffee is non-negotiable apparently. Glad you both found the hour."
      }
    ]
  },
  "monolith": {
    "site": {
      "name": "Quarry & Co.",
      "tagline": "We build sturdy things on purpose."
    },
    "author": {
      "name": "Dana Reyes",
      "bio": "Principal at Quarry & Co. Designs the system, then proves it in code."
    },
    "menuCategories": [
      "Work",
      "Process",
      "Notes"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>Quarry & Co. is a two-discipline studio: one half design, one half engineering, no handoff in between. We take products from a blank canvas to something that ships, holds up under traffic, and reads clearly to the people using it. Small team, direct line, no account managers.</p><p>We work in short, honest cycles. A week of sketching becomes a week of building, and the build talks back. Most of what we ship started as a constraint we refused to design around until we understood it. That stubbornness is the method.</p><p>If you have a hard problem and a real deadline, we are good company. If you want a deck and a maybe, we are probably not.</p>"
      }
    ],
    "posts": [
      {
        "title": "Casework: A Dashboard That Earns Its Density",
        "category": "Work",
        "image": "slate",
        "excerpt": "Forty metrics, one screen, and a redesign that made density a feature instead of an apology.",
        "content": "<p>The brief was blunt: operators live in this dashboard for eight hours a day, and the old one made them squint. Our predecessors had solved the crowding by hiding things behind tabs. The operators solved it by keeping six tabs open at once.</p><h2>Density is not the enemy</h2><p>So we stopped fighting the density and started ranking it. Every number on the screen got a job: glance, scan, or drill. Glance metrics are huge and dumb. Scan metrics live in tight rows you can read with a saccade. Drill metrics hide one click deep, and only there.</p><blockquote>The screen should feel busy the way a cockpit feels busy: everything present, nothing shouting.</blockquote><p>Three weeks in, support tickets about \"where did X go\" dropped to zero, because nothing went anywhere. It just learned its place.</p>"
      },
      {
        "title": "Building a Design System Nobody Has to Babysit",
        "category": "Process",
        "image": "dusk",
        "excerpt": "Tokens, a strict component contract, and the boring discipline that keeps a system alive after launch.",
        "content": "<p>Most design systems die the same way: a hero ships it, the hero leaves, and within a quarter every team has forked a button. We wanted one that survived its own founders.</p><p>The trick was making the right thing the easy thing. Components ship with their constraints baked in, not documented in a wiki nobody reads. You cannot pass a color that is not a token. You cannot set a spacing value off the scale. The system says no before a review has to.</p><ul><li>One source of truth for tokens, consumed by both Figma and code.</li><li>Components that fail loudly in dev when used wrong.</li><li>A changelog written for humans, not for the linter.</li></ul><p>A year later the original team had rotated out and the system was still coherent. That is the whole win. Not elegance, durability.</p>"
      },
      {
        "title": "On Systems: The Cost of a Clever Abstraction",
        "category": "Notes",
        "image": "tide",
        "excerpt": "Every abstraction is a loan against the next engineer's understanding, and the interest compounds.",
        "content": "<p>We deleted four hundred lines of \"flexible\" config last month and replaced it with two hundred lines of plain code. The product did exactly the same thing afterward. It just stopped requiring a tour guide.</p><p>Abstractions are not free. They buy you reuse and they charge you comprehension. The bill comes due the first time someone new has to change behavior the abstraction did not anticipate, and they always have to.</p><blockquote>Write the dumb version first. Earn the clever one with a second use case, not a hunch.</blockquote><p>This is not a case against abstraction. It is a case for paying for it honestly, when you can name the thing it saves you and not a minute before.</p>"
      },
      {
        "title": "Building the Render Pipeline Twice",
        "category": "Work",
        "image": "dusk",
        "excerpt": "We shipped the fast version, watched it break under real data, then rebuilt it to be slow in the right places.",
        "content": "<p>The first pipeline was a sprint. It rendered everything eagerly, looked great in the demo, and fell over the moment a customer fed it ten thousand records instead of ten.</p><p>The rebuild was less glamorous and far more useful. We made it lazy by default and eager only where the eye actually lands first, above the fold and nowhere else. The hero paints instantly; the long tail loads as you reach it.</p><h2>Slow on purpose</h2><p>Performance work is mostly deciding what is allowed to be slow. Once we let the bottom of the page be lazy, the top of the page got to be instant. The customer with ten thousand records never noticed the pipeline at all, which is exactly the review we wanted.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Lindqvist",
        "content": "The glance/scan/drill framing finally gave me language for an argument I have been losing in design reviews for years. Stealing this."
      },
      {
        "author": "Priya N.",
        "content": "We inherited the exact dashboard you are describing, six tabs and all. Sending this to my lead."
      },
      {
        "author": "Theo",
        "content": "\"The screen should feel busy the way a cockpit feels busy.\" That line reframed the whole project for me. Thank you."
      }
    ]
  },
  "verdant": {
    "site": {
      "name": "Fernwell Studio",
      "tagline": "A garden, a studio, and slow seasons of care."
    },
    "author": {
      "name": "Maren Hollis",
      "bio": "Herbalist and garden teacher tending a small backyard studio in the Finger Lakes."
    },
    "menuCategories": [
      "Wellness",
      "Garden",
      "Studio"
    ],
    "pages": [
      {
        "title": "About",
        "image": "sand",
        "content": "<p>Fernwell Studio started as a single raised bed and a kettle that never quite stopped whistling. I am Maren Hollis, and for the better part of fifteen years I have been learning the slow trades: growing herbs, drying them in paper bundles, and teaching small groups how to make their kitchens feel a little more like a remedy and a little less like a rush.</p><p>The studio sits behind the garden, a converted shed with good light and a long worktable that has held more chamomile than I can count. We keep our classes small on purpose. There is no app to download, no streak to maintain, no leaderboard. Just a season, a plant, and the people who showed up to learn it.</p><p>If you are tired in the way that sleep does not fix, you are exactly who this place is for. Come sit by the rosemary. We will figure out the rest together.</p>"
      }
    ],
    "posts": [
      {
        "title": "What to Plant When You Want to Slow Down",
        "category": "Garden",
        "image": "grove",
        "excerpt": "A short list of forgiving, fragrant herbs for the gardener who needs the garden to be gentle this year.",
        "content": "<p>Not every season is a season for ambition. Some years the right goal is simply to keep a few green things alive and let them ask very little of you in return. These are the plants I hand to anyone who tells me they want a garden but cannot promise it much.</p><h2>The forgiving four</h2><ul><li><strong>Mint</strong> — nearly impossible to kill, happy in a pot, and a fistful of it turns a glass of water into something you actually want to drink.</li><li><strong>Calendula</strong> — cheerful, self-seeding, and the petals dry beautifully for winter teas.</li><li><strong>Thyme</strong> — low, woody, and content with poor soil and your forgetfulness.</li><li><strong>Lemon balm</strong> — crush a leaf when you walk past and your whole afternoon resets.</li></ul><p>Plant two of these, not all four. The garden is not a checklist. It is a small standing invitation to step outside and notice that something is still growing, even on the days you are not.</p>"
      },
      {
        "title": "The Five-Minute Tea Ritual That Actually Sticks",
        "category": "Wellness",
        "image": "ember",
        "excerpt": "Why a warm cup at the same hour each day does more for a frayed nervous system than any elaborate routine.",
        "content": "<p>People come to the studio wanting a wellness overhaul. They leave with a teaspoon of dried chamomile and one instruction: make it at the same time every day. The boring part is the part that works.</p><blockquote>A ritual is not what you do once with great feeling. It is what you do a hundred times with very little.</blockquote><p>Pick an hour that already exists in your day, the gap before the kids wake or the quiet after dinner, and pour hot water over something fragrant. Hold the cup before you drink it. That pause, repeated, teaches your body that the day has a place to rest. No app reminder, no streak to break, just a warm cup and a returning.</p><p>Start with chamomile and lemon balm in equal parts. After a week, you will not need me to tell you what it is for.</p>"
      },
      {
        "title": "Inside the Studio: How a Class Comes Together",
        "category": "Studio",
        "image": "sand",
        "excerpt": "A look behind the worktable at how a single afternoon of herb-drying takes shape, from harvest to paper bundle.",
        "content": "<p>People sometimes ask what happens before a class, as if there is a curtain to pull back. There is, a little. The morning of a session, I am in the garden by seven, cutting while the dew has lifted but the heat has not yet arrived. Herbs picked in that narrow window keep their oils, and the studio smells of it for days.</p><p>By mid-morning the long table is set: twine, brown paper, scissors worn smooth, and small jars labeled in my own uneven hand. We work slowly and we talk, and somewhere around the second cup of tea the room loosens. That is the real lesson, not the drying technique but the permission to do one thing carefully and let it take the time it takes.</p><p>Classes run small, six seats at most. If you have been meaning to come, the autumn sessions open next month.</p>"
      },
      {
        "title": "Putting the Garden to Bed for Winter",
        "category": "Garden",
        "image": "grove",
        "excerpt": "The quiet, satisfying work of closing down the beds, and why a tidy dormancy is its own kind of harvest.",
        "content": "<p>There is a particular peace in the last warm week of autumn, when the work shifts from growing to gathering and then, finally, to rest. Putting the garden to bed is not an ending. It is the part of the cycle where you trade abundance for order, and order, it turns out, is its own comfort.</p><p>Cut the spent stems but leave a few seed heads standing for the birds. Mulch the beds thick. Lift the tender roots you want to keep and tuck the rest under leaves. Then do the thing most of us forget: stop. Stand at the edge of the quiet beds and let yourself feel finished.</p><p>The garden will keep without you all winter. That is the gift dormancy offers, to the soil and, if you let it, to you.</p>"
      },
      {
        "title": "A Honey and Thyme Remedy for the First Cold of the Season",
        "category": "Wellness",
        "image": "ember",
        "excerpt": "A simple kitchen oxymel you can stir together in ten minutes and reach for when the cough arrives.",
        "content": "<p>The first cold of autumn arrives like clockwork, usually the week the windows finally close. This is the remedy I keep on the shelf for it, an old-fashioned oxymel that is mostly honey, a little vinegar, and a generous handful of thyme.</p><h2>How to make it</h2><ul><li>Fill a clean jar a third full with fresh thyme sprigs.</li><li>Add raw honey and apple cider vinegar in roughly equal parts until the jar is full.</li><li>Cap it, shake it, and let it sit somewhere dark for two weeks, shaking when you remember.</li><li>Strain, and keep it in the fridge through the cold months.</li></ul><p>A spoonful in warm water soothes a scratchy throat and tastes far better than anything from the pharmacy. None of this replaces a doctor when you need one, but for the ordinary seasonal scratch, it has earned its place on my shelf for years.</p>"
      }
    ],
    "comments": [
      {
        "author": "Priya",
        "content": "I planted lemon balm after reading this and you were right, I crush a leaf every time I pass it now. Small thing, big difference."
      },
      {
        "author": "Tom R.",
        "content": "The forgiving four is exactly the permission I needed. I always overcommit and then feel guilty when half the bed dies. Two plants it is."
      },
      {
        "author": "Maren Hollis",
        "content": "So glad it landed, Tom. Two well-tended pots beat a neglected garden every time. Come by the studio in spring and I will get you started with starts."
      }
    ]
  },
  "ledger": {
    "site": {
      "name": "The Meridian Review",
      "tagline": "Reporting that holds its ground."
    },
    "author": {
      "name": "Eleanor Brandt",
      "bio": "Lead writer and founding editor of The Meridian Review, covering the seams where culture, money, and power overlap."
    },
    "menuCategories": [
      "Culture",
      "Business",
      "Opinion",
      "Politics"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>The Meridian Review began with a stubborn premise: that the most important stories live in the overlap between beats, not inside any single one of them. A culture story is usually a business story wearing better clothes. A political fight is almost always an argument about who pays. We exist to report from that overlap, plainly and without flinching.</p><p>We are a small newsroom by design. We would rather publish four pieces a week that we can defend line by line than forty we cannot. Every article here is reported, edited, and stood behind by a named human being. We do not run anonymous takes, we do not launder press releases into news, and we correct ourselves in public when we get something wrong.</p><p>If you find a claim here that does not hold up, tell us. The masthead is small enough that the email reaches a person, not a void. That is the whole point.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Streaming Wars Are Over. The Audience Lost.",
        "category": "Culture",
        "image": "ember",
        "excerpt": "After a decade of frictionless abundance, the platforms have quietly rebuilt every wall they once promised to tear down.",
        "content": "<p>For roughly ten years, the pitch was simple and seductive: pay one modest fee, and the entire history of filmed entertainment would be yours, anywhere, instantly. That world existed briefly, and it is gone. What replaced it is a thicket of overlapping subscriptions, rotating catalogs, and ad tiers that cost more to remove than the original service once cost to own outright.</p><h2>The math stopped working</h2><p>The turning point was not a single decision but a collective one. Once growth slowed, every platform reached for the same levers at the same time: raise prices, add advertising, crack down on shared logins, and shorten the window before a title vanishes. Each move was rational in isolation. Together they reassembled exactly the friction streaming was supposed to abolish.</p><blockquote>We were sold a library. We rented a turnstile.</blockquote><p>The viewer who once paid nine dollars for near-total access now juggles five services, none complete, and still cannot reliably find the film they wanted on a Tuesday night. The wars are over because the combatants made peace with each other. The terms of that peace were written on the audience's bill.</p>"
      },
      {
        "title": "Inside the Quiet Collapse of the Office Lease",
        "category": "Business",
        "image": "tide",
        "excerpt": "The commercial real estate reckoning everyone predicted has finally arrived, just slowly enough that no one called it a crisis.",
        "content": "<p>There was supposed to be a dramatic moment, a single quarter when the bottom fell out of the office market. It never came. Instead the decline arrived as a slow leak: a lease not renewed here, a floor sublet there, a tower refinanced at terms its owners will spend a decade regretting. The crisis was real. It simply refused to be photogenic.</p><p>What makes the unwinding so hard to read is that the pain is distributed unevenly across the same skyline. A handful of newer, amenity-heavy buildings are nearly full. The older stock behind them sits at occupancy levels that would have triggered headlines in any previous cycle.</p><ul><li>Top-tier towers are competing for a shrinking pool of premium tenants.</li><li>Mid-market buildings face conversions that pencil out for almost none of them.</li><li>Municipal budgets built on commercial property taxes are quietly bracing.</li></ul><p>The result is a market that is not crashing so much as quietly repricing itself, lease by lease, in a language only the people signing the documents can fully read.</p>"
      },
      {
        "title": "Stop Calling It a Talent Shortage",
        "category": "Opinion",
        "image": "dusk",
        "excerpt": "When employers cannot fill a role for two years, the problem is rarely the labor market and almost always the offer.",
        "content": "<p>There is a phrase that has hardened into conventional wisdom across boardrooms and op-ed pages alike: the talent shortage. It is invoked to explain unfilled roles, stalled projects, and missed targets. It is also, in most cases, a polite fiction that lets an employer avoid looking in the mirror.</p><p>A shortage implies that the people simply do not exist. But the workers usually do exist. They have looked at the posting, weighed the pay against the demands, noticed the absence of flexibility or advancement, and quietly declined. That is not scarcity. That is a market clearing exactly as it should, against terms the buyer does not want to accept.</p><blockquote>If no one will take the deal, the deal is the problem.</blockquote><p>Reframing the conversation is not a semantic exercise. As long as the story is shortage, the proposed fixes are pipelines, visas, and training programs aimed at producing more applicants. The moment the story becomes the offer, the fix lands where it belongs: on pay, conditions, and the basic respect a role extends to whoever fills it.</p>"
      },
      {
        "title": "The Local Election No One Covered Is Reshaping the State",
        "category": "Politics",
        "image": "slate",
        "excerpt": "While national attention chased the marquee races, a handful of county boards quietly rewired how millions of people will be governed.",
        "content": "<p>The cameras were pointed somewhere else. On the night the results came in, every major outlet led with the statewide contests, the ones with the recognizable names and the eight-figure ad budgets. Meanwhile, in races that drew a fraction of the coverage, county boards changed hands in ways that will outlast any of the headliners.</p><p>This is the persistent blind spot of modern political reporting. Attention flows to the offices with the biggest titles, but a startling amount of day-to-day governance happens far below them, in bodies that set property assessments, approve infrastructure, and decide how elections themselves are administered.</p><h2>Where power actually sits</h2><p>The new majorities now control decisions that touch residents far more directly than most of what happens in the capital. Zoning that determines whether housing gets built. Budgets that fund or starve local services. Rules that shape how the next election is run.</p><p>None of it was secret. All of it was on the ballot. It simply happened in the part of the room the cameras had decided was not worth filming.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Feld",
        "content": "This finally put words to something I have felt every time I open a streaming app and pay more to watch less. Sharp piece."
      },
      {
        "author": "Priya Nair",
        "content": "The turnstile line is going to stick with me. Would love a follow-up on whether physical media is genuinely coming back or just nostalgia."
      },
      {
        "author": "Dana Whitmore",
        "content": "Refreshing to read something that does not just blame the consumer for cutting the cord. The platforms made these choices on purpose."
      }
    ]
  },
  "nimbus": {
    "site": {
      "name": "Nimbus",
      "tagline": "The product platform for teams that ship without slowing down."
    },
    "author": {
      "name": "Priya Raman",
      "bio": "Head of Product at Nimbus, writing about what we build, why we build it, and what we learn along the way."
    },
    "menuCategories": [
      "Product",
      "Engineering",
      "Company"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>Nimbus started with a frustration most product teams know too well: the gap between having an idea and getting it in front of real users keeps getting wider, not narrower. Every new tool promised speed and quietly added a step. We wanted the opposite.</p><p>So we built a platform that collapses the distance between a rough concept and a shipped feature. Flags, rollouts, metrics, and feedback live in one place, so a small team can move like a focused one and a large team can stay aligned without endless meetings. No glue code, no spreadsheet of who-owns-what, no waiting on a release train.</p><p>We are a remote-first company spread across nine time zones, building in the open and obsessed with the unglamorous details that make software feel fast. If that sounds like your kind of place, the latest from the team is right here on the blog.</p>"
      }
    ],
    "posts": [
      {
        "title": "Introducing Instant Rollouts",
        "category": "Product",
        "image": "dusk",
        "excerpt": "Ship a feature to one percent of users or one hundred, watch it land in real time, and roll it back in a single click.",
        "content": "<p>Today we are launching Instant Rollouts, the fastest way to get a new feature in front of the exact users you want and nobody you don't. You pick the audience, set the percentage, and watch adoption update live on the same screen.</p><h2>Why we built it</h2><p>Releasing software should feel like turning a dial, not pulling a lever you can't reverse. With Instant Rollouts every change is reversible in one click, so the scariest part of shipping, the moment right after you press deploy, becomes the calmest.</p><blockquote>The team that ships small and often always beats the team that ships big and rarely.</blockquote><p>Instant Rollouts is available to every workspace starting today. Open the Releases tab and your first gradual rollout takes about thirty seconds to set up.</p>"
      },
      {
        "title": "How We Cut Cold Start Times by 80 Percent",
        "category": "Engineering",
        "image": "tide",
        "excerpt": "A deep dive into the caching layer, connection pooling, and one embarrassing config flag that was hiding in plain sight.",
        "content": "<p>For months our biggest support theme was the same: the first request after a quiet period felt sluggish. Warm requests were fast, but that initial cold start undermined the whole experience. We decided to fix it for good.</p><p>The investigation surfaced three culprits, and they were not equally guilty:</p><ul><li>Connection pools that drained completely during idle windows</li><li>A cache that warmed lazily instead of on boot</li><li>A single retry flag left at a generous default since our very first deploy</li></ul><h2>The fix that mattered most</h2><p>Pre-warming the cache on boot and keeping a small floor of pooled connections did the heavy lifting. The retry flag was the embarrassing one: flipping a single value shaved hundreds of milliseconds off the tail. Cold starts are now eighty percent faster, and the support theme has quietly disappeared.</p>"
      },
      {
        "title": "Nimbus Raises Series A to Build the Product Layer",
        "category": "Company",
        "image": "ember",
        "excerpt": "We closed a round to invest in reliability, expand the team, and keep our core plan free for small teams.",
        "content": "<p>We are thrilled to share that Nimbus has raised a Series A. More than the number, what excites us is what it lets us do: invest deeply in reliability, grow the team thoughtfully, and keep the platform genuinely useful for the smallest teams.</p><p>Three commitments come with this round. We are doubling the engineering team focused on uptime and performance. We are keeping our core plan free for teams of up to five, forever. And we are publishing a public status and incident history so you always know exactly where we stand.</p><p>Thank you to every team that trusted us early, filed a bug, or told us what was missing. You shaped this product more than any pitch deck ever could. The best part is still ahead.</p>"
      },
      {
        "title": "A Simpler Way to Read Your Metrics",
        "category": "Product",
        "image": "dusk",
        "excerpt": "We redesigned dashboards around the one question every team actually asks: is this change working?",
        "content": "<p>Dashboards have a way of growing until nobody trusts them. Ours did too. So we stepped back and asked what people really come to a metrics screen to learn, and the answer was almost always the same single question: is this change working or not?</p><h2>One question, front and center</h2><p>The new dashboard answers that first. Every release now shows a clear before-and-after on the metrics you chose to watch, with a confidence signal so you know whether the difference is real or just noise. The deep, drill-down views are still there, just one tap away instead of crowding the front page.</p><p>It is live for everyone now. We think you will reach for it a lot, and we would love to hear which view you pin first.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Lee",
        "content": "We switched our rollout process to this last week and already caught a bad change before it hit more than a handful of users. The one-click rollback is the part my whole team keeps talking about."
      },
      {
        "author": "Dana Whitfield",
        "content": "Thirty seconds to set up a gradual rollout is not an exaggeration, I timed it. Great launch."
      },
      {
        "author": "Sofia Almeida",
        "content": "Please keep writing these. The honesty about the embarrassing config flag is exactly why I trust your team with our releases."
      }
    ]
  },
  "sonnet": {
    "site": {
      "name": "Mara Ellison",
      "tagline": "Letters and essays from a slow, lamplit life."
    },
    "author": {
      "name": "Mara Ellison",
      "bio": "Essayist and letter-writer. I keep a quiet journal about attention, weather, and the long art of staying."
    },
    "menuCategories": [
      "Essays",
      "Letters",
      "Notes"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>I am Mara Ellison, and this is where I write the long way around. For most of my life I mistook speed for seriousness; I wrote fast, lived fast, and then wondered why so little of it stayed. These pages are my correction. I write at the pace of a kettle coming to the boil, and I publish only when a sentence has stopped fidgeting.</p><p>What you will find here is plain: <em>essays</em> when I have a thought worth turning over in the light, <em>letters</em> when I want to speak to one person rather than a crowd, and <em>notes</em> when something small refuses to be forgotten. I am drawn to dusk, to second drafts, to the particular dignity of ordinary afternoons. I am suspicious of certainty and fond of the word <em>perhaps</em>.</p><p>If a piece here makes your own life feel a half-degree warmer or quieter, then it has done the only work I ask of it. Thank you for reading slowly. It is the kindest thing a stranger can do.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Hour Before the Lamps",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "There is a thin seam of the day, just before the lamps go on, when the world forgets to perform and is simply itself.",
        "content": "<p>There is a particular hour I have spent most of my life trying to describe and most of my life failing to. It arrives in the last violet minutes before the lamps come on, when the rooms of a house are neither lit nor dark but held in a kind of grey suspension, and everything in them — the unwashed cup, the coat over the chair, your own two hands — seems to be waiting politely to be noticed.</p><p>I used to switch the lights on at once, the way you silence a phone that has begun to ring at an inconvenient time. It took me years to understand that the dimness was not an absence of something but a presence; that the hour before the lamps is the only part of the day with nothing to sell you.</p><blockquote>To love the dusk is to make peace with the fact that some things are clearest precisely when you can no longer see them sharply.</blockquote><p>Now I sit in it on purpose. I let the room go soft. I do not reach for the switch until the dark has finished telling me whatever it came to say, and I have found, more often than not, that it came to say: <em>this is enough, this is already enough.</em></p>"
      },
      {
        "title": "A Letter to Whoever Is Awake at Three",
        "category": "Letters",
        "image": "slate",
        "excerpt": "I do not know your name, but I know the ceiling you are staring at, and I wanted to write to you before morning argues you out of it.",
        "content": "<p>Dear you,</p><p>I am writing this at the hour you are most likely reading it — that long, charcoal stretch after the world has gone to bed and before it has the decency to wake. I know the ceiling you are studying. I have memorised my own. There is a particular loneliness to being the only lit window on a dark street, and I want you to know that mine is lit too.</p><p>Here is the small, unglamorous thing I have learned about three in the morning: it lies. It tells you that the fear is permanent and the morning is theoretical. Both are untrue. The morning is on its way even now, slow and certain as a tide, and it will bring with it the ordinary mercy of coffee and grey light and a list of things that need doing.</p><p>So hold on, if you can. Not heroically — just the way you hold a railing on the stairs. I am holding mine. We are, the two of us and everyone else with a lamp on tonight, getting through the same long dark by the same dull and reliable trick: one breath, and then the next one.</p><p>Yours, from another lit window,<br>Mara</p>"
      },
      {
        "title": "On Keeping a Commonplace Book",
        "category": "Essays",
        "image": "sand",
        "excerpt": "A commonplace book is not a diary; it is a net you throw across the years to catch the lines that would otherwise drown.",
        "content": "<p>For three hundred years, readers kept what they called a commonplace book: a plain notebook into which they copied, by hand, the sentences that had struck them. Not their own thoughts — other people's. A passage from a sermon, a line of verse, a remark overheard on a coach. It was reading as a kind of harvesting.</p><p>I have kept one for nineteen years now, and it has become the truest autobiography I own. It records not what I did but what I noticed, which is the more honest measure of a life. Turning its pages, I can watch myself change by what I chose to copy out:</p><ul><li>At twenty, I copied things that sounded clever.</li><li>At thirty, things that sounded brave.</li><li>Now I copy things that sound true, which is rarer, and quieter, and almost never clever at all.</li></ul><p>If you have never kept one, begin tonight with a single sentence — any sentence that made you look up from the page. You will not understand for years what you are building. You are building a portrait of your own attention, and one day it will tell you, with great tenderness, exactly who you have been.</p>"
      },
      {
        "title": "The Weather Is a Kind of Company",
        "category": "Notes",
        "image": "dusk",
        "excerpt": "When the rain came in off the hills this afternoon, it felt less like a forecast and more like a visitor I had been expecting.",
        "content": "<p>The rain arrived this afternoon the way an old friend does — without knocking, and exactly when needed. I had been at the desk too long, the sentences had gone stiff, and then the first drops struck the window and the whole grey weight of the sky leaned gently against the glass.</p><p>I think we underrate the weather as company. It asks nothing of us and yet it changes the room. A storm makes the kitchen feel like a harbour. A long still fog turns the morning confessional. Today's rain simply sat with me, drumming its patient fingers on the sill, and I found I could write again — not because it inspired me, but because it was there, and being witnessed by something larger than yourself is a quiet permission to continue.</p><p>It has eased now to a soft, steady hush. I am leaving the window open a crack so I can keep hearing it. Some company you do not want to show out too soon.</p>"
      },
      {
        "title": "What the Slow River Taught Me About Ambition",
        "category": "Essays",
        "image": "sand",
        "excerpt": "The river does not hurry, and yet there is nothing it does not eventually reach.",
        "content": "<p>There is a river near the house, broad and brown and so slow that on a still day you must watch a leaf for a full minute to be sure it is moving at all. For most of my striving years I found it faintly embarrassing, the way one is embarrassed by a relative who has given up. Get on with it, I wanted to tell the water. You have a sea to reach.</p><p>I am older now, and I have come to think the river is the wisest thing in the valley. It is not lazy; it is certain. It has made an unspoken bargain with time that the fast streams up in the hills have not: it has agreed to be patient in exchange for being unstoppable.</p><blockquote>Ambition, I think, is mostly a quarrel with time — a refusal to believe you will get there if you do not run. The river has settled the quarrel. It simply keeps going, and arrives.</blockquote><p>I walk down to it most evenings now, in the violet hour, and I try to learn the lesson again, because I keep forgetting it. Move at the pace of your own depth. Carry what you carry without splashing. Trust that slow and unceasing will, in the end, wear down stone — and reach, with no hurry at all, the wide and waiting sea.</p>"
      }
    ],
    "comments": [
      {
        "author": "Imogen Hart",
        "content": "I read this in exactly the hour you describe, with the lamps still off, and could not bring myself to turn them on until I had finished. Thank you for naming something I have felt for years without words."
      },
      {
        "author": "Daniel Reyes",
        "content": "\"Nothing to sell you\" — that line stopped me cold. I am going to try sitting in the dusk on purpose this week instead of reaching for the switch."
      },
      {
        "author": "Wren Okafor",
        "content": "There is a generosity in the way you write that I find rare. This felt less like reading an essay and more like being kept company. I will be back at dusk tomorrow."
      }
    ]
  },
  "dispatch": {
    "site": {
      "name": "The Dispatch",
      "tagline": "Clear reporting for a fast-moving city."
    },
    "author": {
      "name": "Elena Vargas",
      "bio": "Elena Vargas is the founding editor of The Dispatch. She spent a decade covering city hall and transit before building a newsroom focused on plain-language reporting that respects readers' time."
    },
    "menuCategories": [
      "Politics",
      "Tech",
      "Climate",
      "Culture"
    ],
    "pages": [
      {
        "title": "About The Dispatch",
        "image": "slate",
        "content": "<p>The Dispatch is an independent digital newsroom built on a simple promise: tell people what happened, why it matters, and what comes next, without the noise. We cover the city the way readers actually live in it, from the council chamber to the last train home.</p><h2>What we stand for</h2><p>We believe local reporting is infrastructure. A city that can see itself clearly makes better decisions. So we color-code our coverage by beat, keep our headlines honest, and never bury the point three paragraphs down.</p><blockquote>If a story can be told in plain language, it should be. Clarity is not the enemy of depth.</blockquote><h2>How we work</h2><ul><li>Every story names its sources and shows its math.</li><li>We correct mistakes openly and quickly.</li><li>We do not run anything we would not be comfortable explaining to a reader's face.</li></ul><p>Have a tip, a correction, or a story we missed? The newsroom reads everything. Reach out and tell us what is happening on your block.</p>"
      }
    ],
    "posts": [
      {
        "title": "City unveils five-year transit overhaul plan",
        "category": "Politics",
        "image": "ember",
        "excerpt": "The proposal would add three rapid lines and rebuild four aging stations over the next decade.",
        "content": "<p>City officials on Friday unveiled the most ambitious transit plan in a generation, a five-year blueprint that would add three rapid bus lines, rebuild four aging stations, and extend service into two neighborhoods that have gone underserved for decades.</p><p>The plan, presented at a packed council session, carries an estimated cost of $2.4 billion, funded through a mix of state grants, federal infrastructure money, and a modest fare adjustment phased in over three years.</p><blockquote>This is not a patch. This is a rebuild of how the city moves.</blockquote><p>Supporters called it overdue. Critics questioned the timeline and pressed for guarantees that construction would not strangle the corridors it aims to fix. A final vote is expected before the end of the quarter.</p>"
      },
      {
        "title": "Chipmaker beats quarterly forecast as demand holds",
        "category": "Tech",
        "image": "tide",
        "excerpt": "Strong orders for data-center hardware pushed revenue past analyst expectations.",
        "content": "<p>The region's largest semiconductor employer reported quarterly revenue well ahead of forecasts on Thursday, driven by stubbornly strong demand for data-center hardware even as consumer markets softened.</p><p>Executives credited a long-term supply agreement signed last spring and said hiring at the local fabrication plant would continue through the year.</p><ul><li>Revenue rose 14 percent year over year.</li><li>Data-center orders accounted for most of the gain.</li><li>The company reaffirmed its full-year outlook.</li></ul><p>Analysts cautioned that the broader market remains volatile, but for a city that has tied its fortunes to the plant, the results were a welcome signal.</p>"
      },
      {
        "title": "How to read a heat advisory before the next wave hits",
        "category": "Climate",
        "image": "grove",
        "excerpt": "A plain-language guide to the warnings, the risk levels, and what each one actually asks you to do.",
        "content": "<p>Heat advisories arrive every summer, and every summer readers ask the same question: what am I actually supposed to do about it? Here is a guide that cuts through the jargon.</p><h2>What the levels mean</h2><p>The weather service issues three tiers, and they are not interchangeable. Knowing the difference tells you how seriously to take the forecast.</p><h3>Advisory</h3><p>Conditions are uncomfortable and risky for vulnerable people. Check on older neighbors and limit afternoon exertion.</p><h3>Warning</h3><p>Conditions are dangerous for everyone. Stay indoors during peak hours and hydrate well before you feel thirsty.</p><h2>Where to find relief</h2><ul><li>City cooling centers open when a warning is issued.</li><li>Libraries and recreation centers extend hours during extreme heat.</li><li>Transit hubs post the nearest center on station screens.</li></ul><blockquote>The danger is rarely the single hottest hour. It is the nights that never cool down.</blockquote><p>Bookmark this page before the next wave. When the alert lands, you will already know what it means.</p>"
      },
      {
        "title": "The festival lineup is here, and it leans local",
        "category": "Culture",
        "image": "dusk",
        "excerpt": "This year's bill puts homegrown acts on the main stage, a deliberate shift from the headliner formula.",
        "content": "<p>Organizers released the full festival lineup this week, and the headline is the absence of one. Instead of a single marquee act, this year's bill stacks the main stage with homegrown talent.</p><p>It is a deliberate bet that the city's own scene can carry a three-day event, and early ticket numbers suggest audiences agree.</p><blockquote>We stopped asking who would fly in and started asking who was already here.</blockquote><p>The festival runs the last weekend of the month. Day passes go on sale Monday, with a portion of proceeds funding music programs in city schools.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Lee",
        "content": "Finally a transit plan that names the stations getting rebuilt. Sharing this with my whole block."
      },
      {
        "author": "Priya Nair",
        "content": "The heat advisory guide should be pinned every summer. Saved it for my parents."
      },
      {
        "author": "Devon Brooks",
        "content": "Love that the festival is going local this year. The scene here is overdue for a main stage."
      }
    ]
  },
  "atelier": {
    "site": {
      "name": "Maren & Vale",
      "tagline": "Design studio for objects & identity"
    },
    "author": {
      "name": "Maren Vale",
      "bio": "Maren Vale is an industrial and graphic designer who runs a small two-person studio for considered objects, identities, and the spaces between them. She writes here about process, materials, and the discipline of leaving things out."
    },
    "menuCategories": [
      "Identity",
      "Objects",
      "Spaces",
      "Notes"
    ],
    "pages": [
      {
        "title": "Studio",
        "image": "slate",
        "content": "<p>Maren &amp; Vale is a small design studio working at the seam where graphic and industrial design meet. We make identities, objects, and the quiet systems that hold them together — wordmarks and packaging, tools and furniture, the signage that tells you where to stand.</p><h2>How we work</h2><p>Every project begins with a long look and a short list. We are suspicious of the second idea that arrives too quickly and loyal to the one that survives a week of doubt. We prototype early, in cardboard and ink, because a thing you can hold tells the truth faster than a thing you can only describe.</p><blockquote>Restraint is not the absence of decisions. It is the discipline of making fewer, better ones.</blockquote><h2>What we make</h2><ul><li><strong>Identity</strong> — wordmarks, type systems, packaging, and the small marks that carry a name.</li><li><strong>Objects</strong> — tools and tableware designed for the hand before the shelf.</li><li><strong>Spaces</strong> — wayfinding, exhibition design, and the graphics that live on a wall.</li></ul><p>We take on a handful of projects a year so each one gets the attention it asked for. If that sounds like the kind of work you are after, the door is open.</p>"
      }
    ],
    "posts": [
      {
        "title": "Ferro & Salt — a maker's mark",
        "category": "Identity",
        "image": "ember",
        "excerpt": "A full identity for a small-batch kitchen-tool studio: a wordmark, packaging, and a quiet catalogue.",
        "content": "<p>Ferro &amp; Salt forge kitchen tools the slow way — one tang at a time, stamped and oiled by hand. They came to us with a drawer full of beautiful objects and no way to tell strangers why they mattered. The brief was simple and severe: make the brand feel like the steel, not like a startup.</p><p>We built the identity around a single struck mark — a wordmark cut as if it had been stamped into the metal itself, with the slight unevenness a real die would leave. Everything else stepped back to let the tools do the talking.</p><blockquote>The best packaging for an honest object is the one you almost don't notice until you need it.</blockquote><p>The catalogue is uncoated, single-ink, and bound to lie flat on a bench. It smells, faintly, of the workshop. That was not an accident.</p>"
      },
      {
        "title": "The discipline of leaving things out",
        "category": "Notes",
        "image": "slate",
        "excerpt": "Most design problems are subtraction problems wearing the costume of addition problems.",
        "content": "<p>Clients rarely ask us to remove something. They ask for more — another option, a second logo, a louder call to action. But almost every project we have shipped got better the moment we started taking things away.</p><h2>Subtraction is a skill</h2><p>Anyone can add. Adding feels like progress; it produces visible artifacts you can point at in a meeting. Subtraction feels like loss until the moment it suddenly feels like clarity. Learning to sit in that uncomfortable middle is most of the job.</p><h2>A small test we use</h2><p>Before a layout leaves the studio, we run one pass with a single question for every element on the page:</p><ul><li>Does this earn its space, or is it just afraid of it?</li><li>If I delete it, does anyone notice — and do they miss it?</li><li>Is it here for the reader, or here for my comfort?</li></ul><h2>What's left</h2><p>What survives that pass is usually less than half of what we started with, and twice as confident. The white space we were so nervous about turns out to be the whole point. Quiet is not empty. Quiet is room to breathe.</p><blockquote>You are not finished when there is nothing left to add. You are finished when there is nothing left to take away — and the thing still works.</blockquote><p>It is the least glamorous part of design and the part I would defend to the end.</p>"
      },
      {
        "title": "Bench notes: building the Hartwell table",
        "category": "Objects",
        "image": "grove",
        "excerpt": "A dining table designed to be repaired, not replaced — and what that constraint taught us.",
        "content": "<p>The Hartwell table started with a question from the client that we could not stop thinking about: <em>what happens when it breaks?</em> Most furniture is designed as if it never will. We decided to design ours as if it certainly would.</p><p>Every joint is mechanical — no glue where a bolt would do, no bolt where a wedge would do. A leg can be replaced in twenty minutes with one tool. The tabletop is a set of planks held in tension, so a scorched or split board lifts out without touching its neighbours.</p><blockquote>An object you can repair is an object you are allowed to love carelessly.</blockquote><p>It is heavier and slower to make than a glued equivalent, and it will outlive everyone in this room. That trade felt worth writing down.</p>"
      },
      {
        "title": "Wayfinding for a building that hides its doors",
        "category": "Spaces",
        "image": "tide",
        "excerpt": "A converted mill with no obvious entrance, and the sign system that learned to point.",
        "content": "<p>The old Brearley mill is a gorgeous, baffling building. Three floors, four staircases, and an entrance that everyone — including the people who work there — walks past on their first visit. The owners didn't want signage so much as forgiveness for the architecture.</p><p>We resisted the urge to plaster the brick with arrows. Instead we built a system of low, painted thresholds: a single accent stripe at every decision point, always at hand height, always pointing the way you actually need to go rather than the way the corridor happens to run.</p><h2>Designing for the lost</h2><p>The trick with wayfinding is to design for the person who is already confused, not the person reading the map at the door. By the time someone needs a sign, they have stopped paying attention to anything subtle. So we made the marks quiet but unmissable — the visual equivalent of a hand on your shoulder.</p><p>Six months in, the owners tell us the running joke about the invisible door has finally died. We will take that as a review.</p>"
      }
    ],
    "comments": [
      {
        "author": "Jonah Reyes",
        "content": "The Ferro & Salt catalogue is the first piece of branding in years that made me want to touch a website. The restraint is doing a lot of quiet work here."
      },
      {
        "author": "Priya Anand",
        "content": "\"An object you can repair is an object you are allowed to love carelessly\" — I'm stealing this for every client conversation about cost from now on."
      },
      {
        "author": "Tomas Lindqvist",
        "content": "As someone who has been lost in the Brearley mill more than once, the threshold-stripe approach is genuinely clever. Subtle but you can't miss it once you know to look."
      }
    ]
  },
  "hearth": {
    "site": {
      "name": "The Ember Table",
      "tagline": "Warm food, made by hand, in good company."
    },
    "author": {
      "name": "Marisol Vega",
      "bio": "Chef-owner of The Ember Table. Marisol learned to cook at her grandmother's wood stove in Oaxaca and spent a decade in bakery kitchens before opening a tiny corner café where the coffee is always on. She writes here about the dishes coming out of the kitchen, the farmers who grow them, and why a slow morning is worth protecting."
    },
    "menuCategories": [
      "Breakfast",
      "Small Plates",
      "From the Bar",
      "Sweets"
    ],
    "pages": [
      {
        "title": "Our Story",
        "image": "ember",
        "content": "<p>The Ember Table started with a single cast-iron pan and a stubborn belief: that a neighborhood deserves a kitchen that knows its name. We opened on a rainy Tuesday in a corner storefront with eleven seats, one espresso machine, and a sourdough starter named Hazel who is, frankly, older than the lease.</p><h2>What we believe</h2><p>Food is hospitality made edible. We cook the way we'd cook for someone we love &mdash; a little extra butter, a little more time, and never in a hurry. Almost everything on the menu is made in-house, from the bread we bake before sunrise to the cider we press by hand in the fall.</p><blockquote>We're not trying to be the fanciest table in town. We're trying to be the warmest.</blockquote><h2>Where it comes from</h2><p>We buy from growers we can drive to. The eggs come from a farm twenty minutes north, the greens from a rooftop two blocks over, and the honey from a beekeeper who trades jars for breakfast. When the season turns, the menu turns with it &mdash; so if your favorite dish disappears, trust that something just as good is on its way.</p><p>Pull up a chair. Stay as long as you like.</p>"
      }
    ],
    "posts": [
      {
        "title": "Sunrise Sourdough: The Loaf That Runs Our Mornings",
        "category": "Breakfast",
        "image": "ember",
        "excerpt": "Slow-fermented for two days, blistered in the oven, and gone by ten most mornings.",
        "content": "<p>There's a reason we bake before the sun comes up. Our Sunrise Sourdough takes nearly two full days from flour to crust, and none of those hours can be rushed without the loaf knowing. Hazel, our starter, sets the pace &mdash; and Hazel does not care about your schedule.</p><h2>Two days, one loaf</h2><p>It begins the morning before with a build: a spoonful of starter fed and left to triple. By evening we mix the dough, fold it gently every half hour, and let it rest cold overnight. The slow chill is where the flavor lives &mdash; that faint tang, the open crumb, the crackle when you tear it.</p><ul><li>Stone-milled flour from a regional mill</li><li>A long, cold overnight fermentation</li><li>Baked in a screaming-hot cast-iron pot for that blistered crust</li></ul><h2>How we serve it</h2><p>Toasted thick, with cultured butter and a pinch of flaky salt. Or under two soft eggs and a spoonful of last summer's tomato jam. Either way, get here early &mdash; by ten most mornings, there's nothing left but crumbs and the smell.</p>"
      },
      {
        "title": "The Charred Harvest Bowl Is Back for Autumn",
        "category": "Small Plates",
        "image": "grove",
        "excerpt": "Roasted roots, herb oil, and a squeeze of lemon &mdash; the plate we wait all year for.",
        "content": "<p>When the first crate of knobbly autumn roots lands at the back door, the whole kitchen exhales. The Charred Harvest Bowl is a seasonal regular, and it only shows up when the vegetables are good enough to carry it.</p><p>We roast carrots, beets, and squash hard &mdash; hot enough to caramelize the edges and concentrate the sweetness &mdash; then dress everything in a bright herb oil while it's still warm so the flavors soak in. A spoonful of whipped white beans goes underneath, and a squeeze of lemon wakes the whole thing up.</p><blockquote>The trick isn't a secret ingredient. It's heat, patience, and salt at the right moment.</blockquote><p>It's vegetarian without trying to prove a point, and it eats like a full meal. Come hungry; the bowl does not believe in small portions.</p>"
      },
      {
        "title": "How We Press Spiced Cider by Hand Every Fall",
        "category": "From the Bar",
        "image": "dusk",
        "excerpt": "Orchard apples, clove, and star anise, pressed in small batches when the weather turns.",
        "content": "<p>Every autumn we haul in crates of orchard apples and turn the back of the café into a cider operation. It is sticky, it is loud, and it is one of our favorite weeks of the year.</p><h2>From orchard to glass</h2><p>We use a blend &mdash; tart, sweet, and one bitter variety for backbone &mdash; because a great cider, like a great loaf, is about balance. The apples are washed, crushed, and pressed in small batches the same day they arrive, so nothing sits long enough to dull.</p><h2>The warm version</h2><p>Once pressed, we gently warm the cider with whole spices and let it steep, never boiling.</p><ul><li>Whole cloves for warmth</li><li>Star anise for that faint licorice note</li><li>A strip of orange peel, added at the end</li></ul><p>We serve it steaming on cold afternoons, and spiked with a splash of dark rum once the sun's down. It tastes like the season is finally here.</p>"
      },
      {
        "title": "A Slow Morning Is Worth Protecting",
        "category": "Breakfast",
        "image": "tide",
        "excerpt": "On why we'll never rush you out the door, and why the second cup is on the house.",
        "content": "<p>We get asked, sometimes, why we don't turn tables faster. The honest answer is that we built this place to be the opposite of fast.</p><p>A slow morning &mdash; the kind where the coffee goes cold because you're too deep in conversation to notice &mdash; is one of the small luxuries left that money barely touches. You just need a chair, a window, and someone who won't hover.</p><blockquote>Hospitality isn't speed. It's the feeling that you're allowed to stay.</blockquote><p>So linger. Read the whole paper. Let the kids have the second muffin. The second cup of drip is always on the house, because a refill is the easiest kindness we know how to give. The world will still be there when you're ready for it.</p>"
      }
    ],
    "comments": [
      {
        "author": "Dana Whitfield",
        "content": "The Sunrise Sourdough is the best loaf in the neighborhood, full stop. I plan my whole Saturday around getting there before it sells out."
      },
      {
        "author": "Theo Nakamura",
        "content": "Came for the cider, stayed for two hours, and nobody once made me feel like I should leave. That 'slow morning' post is exactly the vibe in person."
      },
      {
        "author": "Priya Raman",
        "content": "The Charred Harvest Bowl converted my very-much-a-meat-person partner. We've been back three times this month."
      }
    ]
  },
  "orbit": {
    "site": {
      "name": "Nebula",
      "tagline": "The developer platform that turns ideas into shipping product."
    },
    "author": {
      "name": "Dev Okafor",
      "bio": "Founding engineer at Nebula. Writes about build systems, observability, and the unglamorous work of making software ship on time. Ex-platform team, recovering YAML maximalist."
    },
    "menuCategories": [
      "Product",
      "Engineering",
      "Changelog",
      "Guides"
    ],
    "pages": [
      {
        "title": "About Nebula",
        "image": "dusk",
        "content": "<p>Nebula started with a frustration every engineering team knows by heart: the distance between a good idea and a deployed feature is full of friction. Pipelines that flake. Preview environments that take ten minutes to spin up. Dashboards that tell you something broke, but never why.</p><h2>What we build</h2><p>Nebula is a developer platform that collapses that distance. Push a branch and you get a live, shareable preview in seconds. Ship to production and tracing, logs, and metrics are already wired in. Scale from your first commit to ten million requests without rewriting your infrastructure or hiring a platform team to babysit it.</p><h2>How we work</h2><p>We are a small, remote-first team of engineers who would rather show a working build than a polished deck. Every feature we ship runs on Nebula itself, so when something is slow or confusing, we feel it first. This blog is where we publish our changelog, our engineering write-ups, and the occasional honest post-mortem.</p><blockquote><p>The best infrastructure is the kind you stop thinking about.</p></blockquote><p>If that sounds like the platform you wish you had, you are exactly who we built it for.</p>"
      }
    ],
    "posts": [
      {
        "title": "Nebula 1.0: instant previews for every push",
        "category": "Changelog",
        "image": "ember",
        "excerpt": "Our biggest release yet: a live, shareable environment for every branch, in under five seconds.",
        "content": "<p>Today we are shipping Nebula 1.0, and the headline feature is one we have wanted since day one: every push now spins up a fully live preview environment, not a static snapshot, in under five seconds.</p><p>Open a pull request and Nebula builds your branch, boots the runtime, and hands you a real URL your whole team can click. Reviewers stop guessing from a diff and start using the actual product.</p><h2>What changed under the hood</h2><p>Previews used to share a cold build cache. We rebuilt the layer caching so common dependencies are warm across branches, which is where most of the speedup comes from.</p><ul><li>Branch previews boot in a median of 4.1 seconds</li><li>Tracing and logs are attached to every preview automatically</li><li>Previews tear down on merge, so you are never billed for stale environments</li></ul><blockquote><p>A preview you can actually click changes how a team reviews work.</p></blockquote><p>Upgrade is automatic for all teams. Nothing to configure, nothing to migrate.</p>"
      },
      {
        "title": "How we cut median deploy time to 40ms",
        "category": "Engineering",
        "image": "tide",
        "excerpt": "A deep dive into the caching, diffing, and edge-routing work that made deploys feel instant.",
        "content": "<p>When we say a deploy takes 40 milliseconds, people assume we are measuring something convenient. We are not. That is the median time from a healthy build artifact to live traffic on the new version. Here is how we got there.</p><h2>Content-addressed artifacts</h2><p>Every build produces a content-addressed bundle. If a file has not changed, its hash has not changed, so a deploy only moves the bytes that are actually new. Most deploys touch a handful of chunks.</p><h2>Atomic edge swaps</h2><p>The cutover itself is a pointer flip at the edge. Old version and new version both exist; we atomically repoint the router and drain the old one in the background.</p><h3>What this unlocks</h3><p>Rollbacks are also pointer flips, which means they are just as fast as deploys. There is no rebuild, no waiting.</p><ul><li>Deploys and rollbacks are symmetric and near-instant</li><li>No partial-deploy windows where two versions serve inconsistent assets</li><li>Traffic shifting is a routing decision, not a build decision</li></ul><blockquote><p>The fastest deploy is the one that moves almost nothing.</p></blockquote><p>The honest caveat: cold builds still take real time. The 40ms number is the cutover, not the build. But the cutover is the part your users feel, and that is the part we obsessed over.</p>"
      },
      {
        "title": "Observability should ship with the runtime, not after it",
        "category": "Product",
        "image": "grove",
        "excerpt": "Why we baked traces, logs, and metrics into the platform instead of selling them as an add-on.",
        "content": "<p>Most teams bolt observability on after an outage. You ship the feature, it breaks in a way nobody predicted, and then you spend a weekend wiring up tracing so it never surprises you again. We think that order is backwards.</p><p>In Nebula, every service you deploy is instrumented from the first request. Distributed traces, structured logs, and the core metrics are there before you write a single line of monitoring code.</p><h2>Find the slow span first</h2><p>When a request is slow, you should not have to reproduce it locally to understand it. Click the trace, see the span tree, and the offending database call is right there with its timing and arguments.</p><p>It is the difference between debugging with evidence and debugging with a hunch.</p><blockquote><p>You cannot fix what you cannot see, and you should not have to pay extra to see it.</p></blockquote><p>This is included in every plan, including the free tier. Observability is not a premium feature. It is table stakes for running software you can trust.</p>"
      },
      {
        "title": "A practical guide to scaling without a platform team",
        "category": "Guides",
        "image": "slate",
        "excerpt": "Five habits that let a small team handle ten million requests without hiring infra specialists.",
        "content": "<p>You do not need a dedicated platform team to run software at scale. You need a few good defaults and the discipline to lean on them. Here is what has worked for the teams shipping on Nebula.</p><h2>Let autoscaling do its job</h2><p>The most common scaling mistake is over-provisioning out of fear. Set sane minimums and maximums, then trust the platform to follow the traffic. Watch it for a week before you touch a knob.</p><h2>Make rollbacks boring</h2><p>If rolling back is scary, you will hesitate during an incident, and hesitation is what turns a blip into an outage. Practice rollbacks until they are a non-event.</p><h2>Budget your queries, not just your servers</h2><p>Compute is cheap to scale. The database is where small teams hit a wall. Put a ceiling on slow queries and treat that ceiling as a feature, not a limitation.</p><ul><li>Set autoscaling minimums and maximums, then leave them alone</li><li>Rehearse rollbacks until they are routine</li><li>Cap and alert on slow queries before they cascade</li><li>Cache aggressively at the edge for read-heavy paths</li><li>Run a load test before the launch, not after the incident</li></ul><p>None of this requires a specialist. It requires picking defaults you trust and resisting the urge to tinker.</p>"
      }
    ],
    "comments": [
      {
        "author": "Priya Raman",
        "content": "The 40ms deploy post is the most honest infra write-up I have read in a while. Appreciate that you separated the cutover from the cold build instead of hiding behind the headline number."
      },
      {
        "author": "Marco Devlin",
        "content": "We switched two services over last month purely for the instant previews. My reviewers actually click the links now instead of approving from the diff. Game changer for our PR process."
      },
      {
        "author": "Sasha Lindqvist",
        "content": "Bundling observability into the free tier is the right call. Every other platform treats traces like a luxury and then acts surprised when teams fly blind."
      }
    ]
  },
  "manual": {
    "author": {
      "name": "Priya Raman",
      "bio": "Developer-experience engineer and technical writer. Priya builds the docs, SDKs, and onboarding flows that make a platform feel obvious in five minutes - and writes about doing it well."
    },
    "site": {
      "name": "Northstar Docs",
      "tagline": "Build, ship, and document - everything you need in one calm, searchable place."
    },
    "menuCategories": [
      "Getting Started",
      "Core Concepts",
      "Guides",
      "API Reference"
    ],
    "comments": [
      {
        "author": "Marcus Lee",
        "content": "The five-minute deploy guide is exactly what I needed - had a live build before my coffee went cold. The copy button on every code block is a small thing that saves a lot of friction."
      },
      {
        "author": "Dana Whitfield",
        "content": "Bookmarking the data-model page. The \"keep the schema small on purpose\" section reframed how I was about to design our resources. More of this, please."
      },
      {
        "author": "Priya Raman",
        "content": "Thanks both! The webhooks reference is next on the list - if there's an event you wish we documented, drop it in the notes and I'll fold it in."
      }
    ],
    "pages": [
      {
        "title": "About",
        "image": "tide",
        "content": "<p>Northstar Docs is the home for everything you need to build on the Northstar platform: setup guides, core concepts, hands-on tutorials, and a complete API reference - all in one calm, searchable place.</p><h2>What you'll find here</h2><p>The left-hand navigation tracks where you are in the docs at all times, and every long page carries an \"On this page\" outline so you can jump straight to the section you need. Code samples are copy-ready, and the search box (press <code>/</code> to focus it) reaches every page.</p><h2>Who writes it</h2><p>These docs are maintained by the developer-experience team, led by Priya Raman. We treat documentation as part of the product: if something here is unclear, wrong, or missing, that's a bug - and we want to hear about it.</p><blockquote><p>Good docs aren't a transcript of the code. They're the shortest honest path from a reader's question to a working result.</p></blockquote><h2>How to contribute</h2><ul><li>Found a typo or a broken example? Leave a note on the page.</li><li>Wish a concept were explained differently? Tell us what tripped you up.</li><li>Building something with Northstar? We love linking to community guides.</li></ul>"
      }
    ],
    "posts": [
      {
        "title": "Your first deploy in five minutes",
        "category": "Getting Started",
        "image": "ember",
        "excerpt": "From zero to a live build with one command - no config to write, nothing to wire up first.",
        "content": "<p>The fastest way to understand Northstar is to ship something with it. This guide takes you from an empty folder to a live deployment in about five minutes, with a single command and no configuration to write up front.</p><h2>Install the CLI</h2><p>Everything starts with the command-line tool. You don't need to install it globally - <code>npx</code> will fetch it on demand:</p><pre><code>npx northstar init</code></pre><p>This scaffolds a minimal project and prints the two commands you'll use most: <code>dev</code> for local work and <code>deploy</code> when you're ready to go live.</p><h2>Run it locally</h2><p>Start the dev server and open the URL it prints. Changes reload instantly, so you can edit and watch in the same screen.</p><pre><code>npx northstar dev</code></pre><h2>Deploy</h2><p>When the local version looks right, one command pushes it to a real, sharable URL:</p><pre><code>npx northstar deploy</code></pre><blockquote><p>You can deploy as many times as you like - every deploy is immutable and gets its own URL, so rolling back is just pointing at an older one.</p></blockquote><p>That's the whole loop. From here, the <a href=\"#\">Configuration</a> guide shows how to tune builds, and <a href=\"#\">Core Concepts</a> explains what's happening under the hood.</p>"
      },
      {
        "title": "The data model, and why we keep the schema small",
        "category": "Core Concepts",
        "image": "grove",
        "excerpt": "How resources relate, and why the schema stays deliberately small.",
        "content": "<p>Northstar has exactly three first-class resources: <strong>projects</strong>, <strong>builds</strong>, and <strong>deployments</strong>. Almost everything you do maps onto one of them, and that smallness is a feature, not a limitation.</p><h2>The three resources</h2><ul><li><strong>Project</strong> - the long-lived container. It owns settings, secrets, and history.</li><li><strong>Build</strong> - one immutable compilation of your source at a point in time.</li><li><strong>Deployment</strong> - a build that has been promoted to a live URL.</li></ul><h2>How they relate</h2><p>A project has many builds; a build can back many deployments. Promoting a build to production never mutates it - it creates a new deployment that points at the existing build. That's why rollbacks are instant and safe.</p><h2>Why so few?</h2><p>Every concept you add to a system is a concept every reader has to learn, every integration has to model, and every bug report has to disambiguate.</p><blockquote><p>A small schema is a promise: there are only a few moving parts, and you can hold all of them in your head.</p></blockquote><p>When you reach for a fourth resource, the question we ask first is whether it's really an attribute of one of these three. Usually it is.</p>"
      },
      {
        "title": "Authentication: tokens, scopes, and rotation",
        "category": "Guides",
        "image": "dusk",
        "excerpt": "Issue scoped tokens, use them safely in CI, and rotate them without downtime.",
        "content": "<p>Northstar uses bearer tokens for every authenticated request. This guide covers how to create them, how to scope them tightly, and how to rotate them without breaking a running pipeline.</p><h2>Creating a token</h2><p>Generate a token from the CLI and copy it once - it is shown only at creation time:</p><pre><code>northstar tokens create --name ci --scope deploy:write</code></pre><h2>Scoping</h2><p>Always issue the narrowest scope that does the job. A deploy pipeline rarely needs read access to secrets, and a status checker never needs write access at all.</p><ul><li><code>deploy:write</code> - create and promote deployments.</li><li><code>builds:read</code> - inspect build status.</li><li><code>secrets:write</code> - manage environment secrets (use sparingly).</li></ul><h2>Using a token in CI</h2><p>Store the token as a secret in your CI provider and pass it through the environment, never inline:</p><pre><code>export NORTHSTAR_TOKEN=$CI_SECRET\nnorthstar deploy</code></pre><h2>Rotating without downtime</h2><p>Create the replacement token first, swap it into your secret store, confirm a deploy succeeds, and only then revoke the old one.</p><blockquote><p>Rotate on a schedule, not just after an incident. A token you rotate quarterly is one you already know how to rotate during a 2 a.m. emergency.</p></blockquote>"
      },
      {
        "title": "Configuration reference",
        "category": "Getting Started",
        "image": "slate",
        "excerpt": "Every option in northstar.config.js, with copy-paste defaults that just work.",
        "content": "<p>Most projects need no configuration at all - the defaults are designed to be correct for a typical build. When you do need to tune something, it lives in a single file, <code>northstar.config.js</code>, at the root of your project.</p><h2>A complete example</h2><pre><code>module.exports = {\n  output: 'dist',\n  build: 'npm run build',\n  env: ['API_URL'],\n  regions: ['iad', 'fra'],\n};</code></pre><h2>The options that matter most</h2><ul><li><code>output</code> - the folder to deploy. Defaults to <code>dist</code>.</li><li><code>build</code> - the command that produces it. Defaults to your package's build script.</li><li><code>env</code> - names of environment variables to expose at build time.</li><li><code>regions</code> - where to serve from. More regions means lower latency, not more cost.</li></ul><h2>Per-environment overrides</h2><p>Anything in the config can be overridden per environment from the dashboard, so you don't have to fork the file for staging versus production.</p><blockquote><p>If you find yourself adding a lot of config, that's a signal worth listening to - tell us, because a sensible default probably belongs in the platform instead.</p></blockquote>"
      }
    ]
  },
  "almanac": {
    "site": {
      "name": "Fieldnotes",
      "tagline": "Notes I tend in the open — interlinked, evergreen, always growing."
    },
    "author": {
      "name": "Wren Calloway",
      "bio": "Researcher and lifelong note-keeper. Wren tends a public second brain in the open, linking small ideas until they grow into bigger ones. Writes about thinking tools, attention, and the slow craft of keeping notes that link to other notes."
    },
    "menuCategories": [
      "Second Brain",
      "Practice",
      "Tools",
      "Field Reports"
    ],
    "pages": [
      {
        "title": "About this garden",
        "image": "grove",
        "content": "<p>This is a digital garden, not a blog. A blog is a stream — newest first, then gone. A garden is tended: notes are planted small, linked to their neighbours, and quietly revised for years. Some of what you read here is freshly seeded and rough. Some has been weeded and watered a dozen times. The stamps at the top of each note tell you which is which: <strong>Planted</strong> is the day an idea first went in the ground; <strong>Last tended</strong> appears only once I've genuinely come back and reworked it.</p><h2>How to wander</h2><p>Don't read top to bottom. Follow the threads. Every internal link is a path I've already walked between two ideas — the dotted underline lighting up means \"this connects to something.\" Tags are the other map: a tag like <em>#attention</em> or <em>#zettelkasten</em> gathers every note touching that thread, regardless of when it was planted.</p><blockquote><p>A note is only as alive as the links leaving it. The garden, not the note, is the unit of thought.</p></blockquote><h2>Why in public</h2><ul><li>Writing for an imagined reader forces me to actually finish a thought.</li><li>Public notes get corrected — kind strangers leave better sources in the margin.</li><li>Ideas compound when they can see each other. A private folder can't link to itself in surprising ways.</li></ul><p>If something here is wrong, half-baked, or missing a connection you can see — that's the point. Leave a note in the margin, and help me tend it.</p>"
      }
    ],
    "posts": [
      {
        "title": "Notes that link to other notes",
        "category": "Second Brain",
        "image": "grove",
        "excerpt": "A note is only as alive as the threads leaving it — tend the links, and the garden starts thinking for you.",
        "content": "<p>The first year I kept notes, I kept them like a hoarder: capture everything, file it deep, never look again. The folder grew; the thinking didn't. The fix wasn't a better app. It was a single rule — <strong>every note must link to at least one other note before I close it.</strong></p><h2>Why the link matters more than the note</h2><p>A lone note is a fact. A linked note is a relationship, and relationships are where ideas actually live. When I force a link, I'm forced to ask: <em>what does this remind me of? what does it argue with? what does it depend on?</em> That question does the real work. The note is just the residue.</p><blockquote><p>You don't have a thought until you can say what it links to.</p></blockquote><h2>The practice, in three moves</h2><ul><li><strong>Capture small.</strong> One idea per note. If it has two ideas, it's two notes that link to each other.</li><li><strong>Link before you leave.</strong> Don't save a note until it points at a neighbour. No orphans.</li><li><strong>Tend on contact.</strong> Every time you re-open a note to link a new one, fix a sentence while you're there.</li></ul><p>Do this for a few weeks and the structure stops being a filing system and starts being a map. You'll search for one note and arrive, three links later, at a connection you never consciously made. That surprise is the garden thinking back at you.</p>"
      },
      {
        "title": "Write the note before you forget the why",
        "category": "Practice",
        "image": "sand",
        "excerpt": "Capture the reason a thing mattered, not just the thing — the why decays faster than the what.",
        "content": "<p>The bookmark is a lie we tell ourselves. We save the article, the quote, the tab — and we believe we've saved the <em>insight</em>. We haven't. We've saved the artifact and lost the spark, because the reason it lit us up is already evaporating.</p><p>The half-life of a <em>why</em> is about a day. The <em>what</em> can wait; the <em>why</em> cannot. So I write the note while the feeling is still warm, and I lead with the reason: <strong>\"This matters because…\"</strong></p><blockquote><p>Future-you is a stranger who inherited your bookmarks with none of your context. Write for that stranger.</p></blockquote><p>It takes ninety seconds. One sentence of why, one line of what, one link to a neighbour. That's a real note. Everything I skipped this step on is now a graveyard of links I'm afraid to open, because I no longer remember the person who saved them.</p>"
      },
      {
        "title": "Tending versus publishing",
        "category": "Practice",
        "image": "tide",
        "excerpt": "A blog ships and forgets; a garden revisits — and the metadata should tell you which note is fresh.",
        "content": "<p>Publishing is an event. You hit the button, the post goes out, and it begins its slow slide down the timeline into the dark. Tending is the opposite posture: you come <em>back</em>. You weed a paragraph that aged badly, you graft on a link to something you learned last month, you let the note keep up with you.</p><h2>The problem this theme solves</h2><p>Most blogs only show a publish date, which quietly punishes tended notes — a five-year-old note that you revised yesterday <em>looks</em> stale. So Almanac stamps two dates: when a note was <strong>planted</strong> and when it was <strong>last tended</strong>. The second only appears when there's a real revision behind it.</p><ul><li>Readers can trust that \"last tended yesterday\" means something living.</li><li>I'm rewarded for going back, not just for shipping something new.</li><li>Old notes get a second life instead of a slow burial.</li></ul><blockquote><p>Treat your best ideas like perennials, not cut flowers.</p></blockquote><p>It's a small UI choice with an outsized effect on behaviour. Show me a metric and I'll optimise for it. Show me \"last tended\" and I'll actually tend.</p>"
      },
      {
        "title": "Tags are paths, not folders",
        "category": "Tools",
        "image": "dusk",
        "excerpt": "A folder asks where a note belongs; a tag asks what it touches — and a note can touch many things at once.",
        "content": "<p>Folders force a cruel question: <em>where does this one thing go?</em> But interesting notes refuse to live in one place. A note about deliberate practice is also a note about attention, also a note about teaching, also a note about how I learned to swim. A folder makes me amputate four of those to keep one. A tag keeps all five.</p><p>That's why every note here wears its tags out loud — little <em>#hash</em> rows on each card, a thread list at the foot of every note. They aren't decoration; they're the second navigation system, running underneath the links.</p><h2>How I tag without making a mess</h2><ul><li><strong>Tag the thread, not the topic.</strong> <em>#attention</em> is a thread that runs through many notes; <em>#blog-post-march</em> is noise.</li><li><strong>Reuse ruthlessly.</strong> A tag used once is a typo. A tag used thirty times is a map.</li><li><strong>Let tags retire.</strong> If a thread stops growing, stop tagging it. The garden can have fallow beds.</li></ul><blockquote><p>Folders are for things that are finished. Tags are for things that are still alive.</p></blockquote><p>Search finds the note you already know exists. Tags surface the notes you forgot you connected — which is the whole reason to keep a garden in the first place.</p>"
      },
      {
        "title": "Field report: a month of tending in public",
        "category": "Field Reports",
        "image": "ember",
        "excerpt": "Thirty days, sixty notes, and the surprising discovery that strangers weed your garden better than you do.",
        "content": "<p>I spent thirty days publishing every note the day I planted it — rough edges and all — instead of hoarding drafts. Here's the honest field report.</p><p>What I expected: embarrassment. What I got: corrections. A reader pointed me to the original source for a quote I'd half-remembered. Another untangled a claim I'd made about memory that turned out to be folklore. The garden got <em>more</em> accurate by being public, not less.</p><h2>What worked</h2><ul><li>Notes published rough still got linked — done-and-visible beats perfect-and-hidden.</li><li>The \"last tended\" stamp made revisiting feel like progress, so I revisited.</li><li>Tags surfaced two notes I'd written months apart that turned out to be the same idea. I merged them.</li></ul><h2>What didn't</h2><p>I over-tagged in week one — thirty notes, forty tags, half of them used once. I spent a Sunday weeding the tag list down to twelve real threads, and the maps got legible again.</p><blockquote><p>A garden tended in public is slower to plant and faster to grow. The strangers are the gardeners you didn't know you'd hired.</p></blockquote><p>Next month's experiment: stop writing new notes entirely for two weeks and do nothing but tend the old ones. I suspect the best note in here is one I've already planted and simply haven't finished thinking about.</p>"
      }
    ],
    "comments": [
      {
        "author": "Iris Tan",
        "content": "The \"link before you leave\" rule reorganised my whole system in a week. I used to have 400 orphan notes; now everything points somewhere and I actually find things by accident."
      },
      {
        "author": "Marcus Reyes",
        "content": "Small correction for the field report — the memory-as-muscle quote is usually misattributed; the earliest version I can find is from a 1972 lecture, not the book you linked. Happy to send the source."
      },
      {
        "author": "Wren Calloway",
        "content": "Marcus, that's exactly the kind of weeding I hoped for — send it over and I'll tend the note and credit you in the margin. This is the garden working as intended."
      }
    ]
  }
};
