<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MoreNewsSeeder extends Seeder
{
    /**
     * Add 50 additional bilingual news/blog articles. Content is drawn from a
     * pool of realistic templates and given unique slugs and spread-out dates.
     */
    public function run(): void
    {
        $u = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1200&h=700&q=80";

        $images = [
            'photo-1509062522246-3755977927d7',
            'photo-1503676260728-1c00da094a0b',
            'photo-1522202176988-66273c2fd55f',
            'photo-1427504494785-3a9ca7044f45',
            'photo-1546410531-bb4caa6b424d',
            'photo-1571260899304-425eee4c7efc',
            'photo-1524178232363-1fb2b075b655',
            'photo-1497633762265-9d179a990aa6',
        ];

        $templates = [
            [
                'category' => 'events',
                'author' => ['en' => 'Sports Council', 'dv' => 'ކުޅިވަރު ކައުންސިލް'],
                'title' => ['en' => 'Inter-House Athletics Meet', 'dv' => 'ހައުސްތަކުގެ މެދުގައި އެތްލެޓިކްސް މީޓް'],
                'excerpt' => ['en' => 'A day of track and field as the four houses compete.', 'dv' => 'ހަތަރު ހައުސް ވާދަކުރި ދުވުމާއި ފީލްޑް އިވެންޓްތަކުގެ ދުވަހެއް.'],
                'body' => ['en' => 'Our annual Inter-House Athletics Meet filled the grounds with colour and cheer as students competed in sprints, relays and field events. Sportsmanship was on full display throughout the day.', 'dv' => 'އަހަރީ ހައުސް އެތްލެޓިކްސް މީޓްގައި ދަރިވަރުން ދުވުމާއި ރިލޭ އަދި ފީލްޑް އިވެންޓްތަކުގައި ވާދަކުރިއެވެ. ދުވަހުގެ ހުރިހާ ވަގުތެއްގައި ކުޅިވަރުގެ ރޫޙު ފެނިގެންދިޔައެވެ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => 'Islamic Department', 'dv' => 'އިސްލާމީ ދާއިރާ'],
                'title' => ['en' => 'Quran Recitation Competition', 'dv' => 'ޤުރުއާން ކިޔެވުމުގެ މުބާރާތް'],
                'excerpt' => ['en' => 'Students showcased their talent in recitation and tajweed.', 'dv' => 'ދަރިވަރުން ކިޔެވުމާއި ތަޖުވީދުގައި ހުނަރު ދައްކާލި.'],
                'body' => ['en' => 'The annual Quran Recitation Competition brought together students from every grade to recite with beautiful tajweed. We congratulate all participants and winners.', 'dv' => 'އަހަރީ ޤުރުއާން ކިޔެވުމުގެ މުބާރާތުގައި ހުރިހާ ގްރޭޑެއްގެ ދަރިވަރުން ރީތި ތަޖުވީދާއެކު ކިޔެވިއެވެ. ބައިވެރިވި އެންމެންނަށާއި ވަނަތައް ހޯދި ދަރިވަރުންނަށް މަރުޙަބާ.'],
            ],
            [
                'category' => 'schoolNews',
                'author' => ['en' => "Principal's Office", 'dv' => 'ޕްރިންސިޕަލްގެ އޮފީސް'],
                'title' => ['en' => 'Grade 10 Farewell Assembly', 'dv' => 'ގްރޭޑް 10 ގެ ވަދާޢީ އެސެމްބްލީ'],
                'excerpt' => ['en' => 'A heartfelt send-off for our graduating students.', 'dv' => 'ދަސްވެނިވާ ދަރިވަރުންނަށް އަސަރުގަދަ ވަދާޢީ ބައްދަލުވުމެއް.'],
                'body' => ['en' => 'Staff and students gathered to bid farewell to the Grade 10 cohort, celebrating their years of hard work and wishing them success in their examinations and beyond.', 'dv' => 'ގްރޭޑް 10 ގެ ދަރިވަރުންނަށް ވަދާޢު ކިޔުމަށް މުދައްރިސުންނާއި ދަރިވަރުން އެއްވެ، އެ ދަރިވަރުންގެ މަސައްކަތް ފާހަގަކޮށް، އިމްތިޙާނުތަކުގައި ކާމިޔާބީއަށް އެދުނެވެ.'],
            ],
            [
                'category' => 'schoolNews',
                'author' => ['en' => 'Communications Office', 'dv' => 'ކޮމިއުނިކޭޝަން އޮފީސް'],
                'title' => ['en' => 'Parent–Teacher Conference Held', 'dv' => 'ބެލެނިވެރިން-މުދައްރިސުން ބައްދަލުވުން ބާއްވައިފި'],
                'excerpt' => ['en' => 'Families met teachers to discuss student progress.', 'dv' => 'ދަރިވަރުންގެ ކުރިއެރުމާ ބެހޭގޮތުން އާއިލާތައް މުދައްރިސުންނާ ބައްދަލުކުރި.'],
                'body' => ['en' => 'Our termly Parent–Teacher Conference gave families the chance to discuss each child’s progress, strengths and goals with their teachers. Thank you to everyone who attended.', 'dv' => 'ޓާމް ބެލެނިވެރިން-މުދައްރިސުން ބައްދަލުވުމުގައި، ކޮންމެ ދަރިވަރެއްގެ ކުރިއެރުމާއި ލަނޑުދަނޑިތަކާ ބެހޭގޮތުން މުދައްރިސުންނާ މަޝްވަރާކުރުމުގެ ފުރުޞަތު ލިބުނެވެ. ބައިވެރިވި އެންމެންނަށް ޝުކުރިއްޔާ.'],
            ],
            [
                'category' => 'schoolNews',
                'author' => ['en' => 'ICT Department', 'dv' => 'އައިސީޓީ ދާއިރާ'],
                'title' => ['en' => 'Coding Club Launches', 'dv' => 'ކޯޑިން ކްލަބް ފަށައިފި'],
                'excerpt' => ['en' => 'A new club introduces students to programming basics.', 'dv' => 'ދަރިވަރުންނަށް ޕްރޮގްރާމިންގެ އަސާސްތައް ދަސްކޮށްދޭ އައު ކްލަބެއް.'],
                'body' => ['en' => 'The new Coding Club welcomes students eager to learn programming, from block-based games to their first lines of Python. Sessions run every week after school.', 'dv' => 'ބްލޮކް ގޭމްތަކުން ފެށިގެން ޕައިތަން ކޯޑާ ހަމައަށް، ޕްރޮގްރާމިން ދަސްކުރަން ބޭނުންވާ ދަރިވަރުންނަށް އައު ކޯޑިން ކްލަބުން މަރުޙަބާ ކިޔައެވެ. ކޮންމެ ހަފްތާއަކު ސްކޫލަށްފަހު ސެޝަންތައް ކުރިއަށްދާނެއެވެ.'],
            ],
            [
                'category' => 'achievements',
                'author' => ['en' => 'Debate Society', 'dv' => 'ބަހުސް ސޮސައިޓީ'],
                'title' => ['en' => 'Debate Team Reaches Nationals', 'dv' => 'ބަހުސް ޓީމު ޤައުމީ ފެންވަރަށް'],
                'excerpt' => ['en' => 'Our debaters qualify for the national championship.', 'dv' => 'އަޅުގަނޑުމެންގެ ބަހުސް ޓީމު ޤައުމީ ޗެމްޕިއަންޝިޕަށް ކޮލިފައިވެއްޖެ.'],
                'body' => ['en' => 'After a strong regional run, our debate team has qualified for the national championship. Their reasoning and teamwork impressed the adjudicators at every round.', 'dv' => 'ބާރުގަދަ ސަރަޙައްދީ ދަތުރަކަށްފަހު، އަޅުގަނޑުމެންގެ ބަހުސް ޓީމު ޤައުމީ ޗެމްޕިއަންޝިޕަށް ކޮލިފައިވެއްޖެއެވެ. ކޮންމެ ބުރެއްގައިވެސް އެ ދަރިވަރުންގެ ވިސްނުމާއި ޓީމު މަސައްކަތް ފާހަގަކުރެވުނެވެ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => 'Staff Committee', 'dv' => 'މުވައްޒަފުންގެ ކޮމިޓީ'],
                'title' => ['en' => "World Teachers' Day Celebration", 'dv' => 'ދުނިޔޭގެ މުދައްރިސުންގެ ދުވަސް'],
                'excerpt' => ['en' => 'Students honoured their teachers with a special assembly.', 'dv' => 'ދަރިވަރުން ޚާއްޞަ އެސެމްބްލީއަކުން މުދައްރިސުންނަށް ޝުކުރު އަދާކުރި.'],
                'body' => ['en' => 'Students marked World Teachers’ Day with performances, messages of gratitude and a special assembly celebrating the dedication of our teaching staff.', 'dv' => 'ދަރިވަރުން ދުނިޔޭގެ މުދައްރިސުންގެ ދުވަސް ފާހަގަކުރީ ހުށަހެޅުންތަކާއި ޝުކުރުގެ މެސެޖުތަކާއެކު، މުދައްރިސުންގެ ޚިދުމަތް ފާހަގަކުރާ ޚާއްޞަ އެސެމްބްލީއަކުންނެވެ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => 'Environment Club', 'dv' => 'ތިމާވެށި ކްލަބް'],
                'title' => ['en' => 'Beach Clean-up Drive', 'dv' => 'ގޮނޑުދޮށް ސާފުކުރުމުގެ ޕްރޮގްރާމް'],
                'excerpt' => ['en' => 'Volunteers cleared litter from the island shoreline.', 'dv' => 'ދަރިވަރުން ރަށުގެ ގޮނޑުދޮށް ސާފުކޮށްފި.'],
                'body' => ['en' => 'Dozens of student volunteers spent the morning clearing plastic and debris from the island shoreline, sorting recyclables and logging what they collected for a class project.', 'dv' => 'ގިނަ ދަރިވަރުންގެ ސްވޮލަންޓިއަރުން ހެނދުނު ހޭދަކުރީ ރަށުގެ ގޮނޑުދޮށުން ޕްލާސްޓިކާއި ކުނި ސާފުކުރުމުގައެވެ. އަދި ރީސައިކަލް ކުރެވޭ ތަކެތި ވަކިކޮށް، ސާފުކުރި ތަކެތި ކްލާސް ޕްރޮޖެކްޓަކަށް ރެކޯޑްކުރިއެވެ.'],
            ],
            [
                'category' => 'schoolNews',
                'author' => ['en' => 'ICT Department', 'dv' => 'އައިސީޓީ ދާއިރާ'],
                'title' => ['en' => 'New Computer Lab Inaugurated', 'dv' => 'އައު ކޮމްޕިއުޓަރ ލެބް ހުޅުވައިފި'],
                'excerpt' => ['en' => 'A modern lab with 30 workstations opens for students.', 'dv' => '30 ވޯކްސްޓޭޝަނާއެކު ޒަމާނީ ލެބެއް ދަރިވަރުންނަށް ހުޅުވައިފި.'],
                'body' => ['en' => 'Our new computer lab, equipped with 30 modern workstations and high-speed internet, is now open, giving every class hands-on time with digital tools and coding.', 'dv' => '30 ޒަމާނީ ވޯކްސްޓޭޝަނާއި ބާރު ސްޕީޑް އިންޓަނެޓާއެކު އައު ކޮމްޕިއުޓަރ ލެބް މިހާރު ހުޅުވިއްޖެއެވެ.'],
            ],
            [
                'category' => 'achievements',
                'author' => ['en' => 'Examinations Office', 'dv' => 'އިމްތިޙާން އޮފީސް'],
                'title' => ['en' => 'Students Excel in O-Level Results', 'dv' => 'ދަރިވަރުން އޯ-ލެވެލް ނަތީޖާ ރަނގަޅުކޮށްފި'],
                'excerpt' => ['en' => 'Record passes across science and languages this year.', 'dv' => 'މިއަހަރު ސައިންސާއި ބަހުގެ ދާއިރާތަކުން ރެކޯޑް ފާސްތަކެއް.'],
                'body' => ['en' => 'We are proud to share that this year’s O-Level cohort achieved record results across the sciences, mathematics and languages. Congratulations to our students and teachers.', 'dv' => 'މިއަހަރުގެ އޯ-ލެވެލް ދަރިވަރުން ސައިންސާއި ހިސާބާއި ބަހުގެ ދާއިރާތަކުން ރެކޯޑް ނަތީޖާ ހޯދިކަން ފަޚުރުވެރިކަމާއެކު ޚިއްޞާކުރަމެވެ. ދަރިވަރުންނަށާއި މުދައްރިސުންނަށް މަރުޙަބާ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => 'Health Unit', 'dv' => 'ޞިއްޙީ ޔުނިޓް'],
                'title' => ['en' => 'Health and Wellness Week', 'dv' => 'ޞިއްޙަތާއި ދުޅަހެޔޮކަމުގެ ހަފްތާ'],
                'excerpt' => ['en' => 'A week of activities promoting healthy habits.', 'dv' => 'ދުޅަހެޔޮ އާދަތައް ކުރިއެރުވުމުގެ ހަރަކާތްތަކުގެ ހަފްތާއެއް.'],
                'body' => ['en' => 'Health and Wellness Week featured fitness sessions, healthy-eating demonstrations and talks on wellbeing, encouraging students to build lifelong healthy habits.', 'dv' => 'ޞިއްޙަތާއި ދުޅަހެޔޮކަމުގެ ހަފްތާގައި ފިޓްނަސް ސެޝަންތަކާއި، ދުޅަހެޔޮ ކެއުމުގެ ޑިމޮންސްޓްރޭޝަންތަކާއި ވާހަކަދެއްކުންތައް ހިމެނުނެވެ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => 'Arts Department', 'dv' => 'ފަންނުވެރިކަމުގެ ދާއިރާ'],
                'title' => ['en' => 'Art Exhibition Showcases Talent', 'dv' => 'އާޓް އެގްޒިބިޝަނުން ހުނަރު ދައްކާލައިފި'],
                'excerpt' => ['en' => 'Student paintings and crafts on display for families.', 'dv' => 'ދަރިވަރުންގެ ކުރެހުމާއި އުފެއްދުންތައް އާއިލާތަކަށް ދައްކާލައިފި.'],
                'body' => ['en' => 'Our annual Art Exhibition transformed the hall into a gallery of student paintings, crafts and photography, celebrating creativity across every grade.', 'dv' => 'އަހަރީ އާޓް އެގްޒިބިޝަނުން ހޯލް، ދަރިވަރުންގެ ކުރެހުމާއި އުފެއްދުންތަކާއި ފޮޓޯގްރަފީގެ ގެލެރީއަކަށް ބަދަލުކޮށްލިއެވެ.'],
            ],
            [
                'category' => 'achievements',
                'author' => ['en' => 'Sports Council', 'dv' => 'ކުޅިވަރު ކައުންސިލް'],
                'title' => ['en' => 'Football Team Wins Atoll Cup', 'dv' => 'ފުޓްބޯޅަ ޓީމު އަތޮޅު ކަޕް ކާމިޔާބުކޮށްފި'],
                'excerpt' => ['en' => 'A thrilling final secures the title for our school.', 'dv' => 'އަސަރުގަދަ ފައިނަލަކުން ސްކޫލަށް ތަށި ކަށަވަރުކޮށްފި.'],
                'body' => ['en' => 'In a thrilling final, our football team lifted the Atoll Cup after a hard-fought win. Their teamwork and spirit made the whole school proud.', 'dv' => 'އަސަރުގަދަ ފައިނަލެއްގައި، އަޅުގަނޑުމެންގެ ފުޓްބޯޅަ ޓީމު އަތޮޅު ކަޕް އުފުލާލިއެވެ. އެ ދަރިވަރުންގެ ޓީމު މަސައްކަތުން މުޅި ސްކޫލް ފަޚުރުވެރިކުރުވިއެވެ.'],
            ],
            [
                'category' => 'schoolNews',
                'author' => ['en' => 'Library', 'dv' => 'ލައިބްރަރީ'],
                'title' => ['en' => 'Library Reading Challenge', 'dv' => 'ލައިބްރަރީ ކިޔެވުމުގެ ޗެލެންޖް'],
                'excerpt' => ['en' => 'Students read thousands of books this term.', 'dv' => 'ދަރިވަރުން މި ޓާމުގައި އެތައް ހާސް ފޮތެއް ކިޔައިފި.'],
                'body' => ['en' => 'This term’s Reading Challenge saw students collectively read thousands of books in Dhivehi and English, earning badges and celebrating a shared love of reading.', 'dv' => 'މި ޓާމުގެ ކިޔެވުމުގެ ޗެލެންޖުގައި ދަރިވަރުން ދިވެހި އަދި އިނގިރޭސި ބަހުން އެތައް ހާސް ފޮތެއް ކިޔައި، ބެޖްތައް ހޯދިއެވެ.'],
            ],
            [
                'category' => 'events',
                'author' => ['en' => "Principal's Office", 'dv' => 'ޕްރިންސިޕަލްގެ އޮފީސް'],
                'title' => ['en' => 'Independence Day Ceremony', 'dv' => 'މިނިވަން ދުވަހުގެ ރަސްމިއްޔާތު'],
                'excerpt' => ['en' => 'The school marked Independence Day with pride.', 'dv' => 'ސްކޫލުން ފަޚުރުވެރިކަމާއެކު މިނިވަން ދުވަސް ފާހަގަކުރި.'],
                'body' => ['en' => 'Students and staff gathered for a special ceremony to mark Independence Day, with the national flag, patriotic songs and a message on our shared history.', 'dv' => 'މިނިވަން ދުވަސް ފާހަގަކުރުމަށް ދަރިވަރުންނާއި މުވައްޒަފުން ޚާއްޞަ ރަސްމިއްޔާތެއްގައި އެއްވެ، ޤައުމީ ދިދައާއި ޤައުމީ ލަވަތަކާއެކު ފާހަގަކުރިއެވެ.'],
            ],
        ];

        $start = Carbon::create(2026, 7, 30);

        for ($i = 0; $i < 50; $i++) {
            $t = $templates[$i % count($templates)];

            NewsArticle::create([
                'slug' => Str::slug($t['title']['en']).'-'.($i + 1),
                'category' => $t['category'],
                'image' => $u($images[$i % count($images)]),
                'is_published' => true,
                'published_at' => $start->copy()->subDays($i * 5)->format('Y-m-d'),
                'author' => $t['author'],
                'title' => $t['title'],
                'excerpt' => $t['excerpt'],
                'body' => $t['body'],
            ]);
        }
    }
}
