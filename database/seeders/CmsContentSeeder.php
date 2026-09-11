<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\NewsArticle;
use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class CmsContentSeeder extends Seeder
{
    /**
     * Seed the CMS with the site's launch content. Runs only when the tables
     * are empty so it never clobbers content edited through the admin panel.
     */
    public function run(): void
    {
        if (Announcement::count() === 0) {
            $this->seedAnnouncements();
        }

        if (NewsArticle::count() === 0) {
            $this->seedNews();
        }

        if (SiteContent::query()->where('key', 'academic_events')->doesntExist()) {
            $this->seedAcademicEvents();
        }
    }

    /**
     * Slugs are spelled out rather than left to the model. DatabaseSeeder uses
     * WithoutModelEvents, so Announcement's saving hook never runs here and a
     * strict database rejects the insert on the non-null slug column. The news
     * rows below have always named theirs for the same reason.
     */
    private function seedAnnouncements(): void
    {
        $rows = [
            [
                'slug' => 'term-3-exam-timetable-released',
                'category' => 'academic',
                'pinned' => true,
                'published_at' => '2026-06-28',
                'title' => [
                    'en' => 'Term 3 Exam Timetable Released',
                    'dv' => 'ތިންވަނަ ޓާމުގެ އިމްތިޙާން ތާވަލު އާންމުކޮށްފި',
                ],
                'body' => [
                    'en' => 'The final examination timetable for Term 3 is now available on the Downloads page. Exams begin on 20 July. Please review the schedule with your child.',
                    'dv' => 'ތިންވަނަ ޓާމުގެ ފައިނަލް އިމްތިޙާން ތާވަލު މިހާރު ޑައުންލޯޑް ޞަފްޙާއިން ލިބިވަޑައިގަންނަވާނެ. އިމްތިޙާން ފެށޭނީ ޖުލައި 20 ގައި.',
                ],
            ],
            [
                'slug' => 'school-uniform-policy-update',
                'category' => 'general',
                'pinned' => false,
                'published_at' => '2026-06-22',
                'title' => [
                    'en' => 'School Uniform Policy Update',
                    'dv' => 'ސްކޫލް ޔުނިފޯމް ސިޔާސަތު އަޕްޑޭޓް',
                ],
                'body' => [
                    'en' => 'From the new term, house-coloured PE shirts will be worn on activity days. Details are in the updated policy document.',
                    'dv' => 'އައު ޓާމުން ފެށިގެން، ހަރަކާތުގެ ދުވަސްތަކުގައި ހައުސް ކުލައިގެ ޕީއީ ގަމީސް ބޭނުންކުރައްވަން ޖެހޭނެ.',
                ],
            ],
            [
                'slug' => 'annual-sports-day-save-the-date',
                'category' => 'events',
                'pinned' => false,
                'published_at' => '2026-06-15',
                'title' => [
                    'en' => 'Annual Sports Day — Save the Date',
                    'dv' => 'އަހަރީ ކުޅިވަރު ދުވަސް — ތާރީޚު ފާހަގަކުރައްވާ',
                ],
                'body' => [
                    'en' => 'Our Annual Sports Day will be held on 8 August at the island football ground. Families are warmly invited to cheer on the houses.',
                    'dv' => 'އަހަރީ ކުޅިވަރު ދުވަސް އޮގަސްޓް 8 ގައި ރަށު ފުޓްބޯޅަ ދަނޑުގައި ބޭއްވޭނެ. އާއިލާތަކަށް ދަޢުވަތު އަރުވަން.',
                ],
            ],
            [
                'slug' => 'school-reopening-date-confirmed',
                'category' => 'academic',
                'pinned' => false,
                'published_at' => '2026-06-01',
                'title' => [
                    'en' => 'School Reopening Date Confirmed',
                    'dv' => 'ސްކޫލް އަލުން ހުޅުވޭ ތާރީޚު ކަށަވަރުވެއްޖެ',
                ],
                'body' => [
                    'en' => 'School reopens for all grades on Sunday, 3 August 2026. The office will be open from 28 July for enquiries.',
                    'dv' => 'ހުރިހާ ގްރޭޑްތަކަށް ސްކޫލް އަލުން ހުޅުވޭނީ 2026 އޮގަސްޓް 3، އާދިއްތަ ދުވަހު.',
                ],
            ],
            [
                'slug' => 'early-closure-due-to-weather',
                'category' => 'emergency',
                'pinned' => false,
                'published_at' => '2026-05-24',
                'title' => [
                    'en' => 'Early Closure Due to Weather',
                    'dv' => 'މޫސުމް ގޯސްވުމުން އަވަހަށް ބަންދުކުރުން',
                ],
                'body' => [
                    'en' => 'Owing to the weather advisory, school closed at 12:00 PM today. All students were dismissed safely. Normal hours resume tomorrow.',
                    'dv' => 'މޫސުމް ގޯސްވުމުގެ ސަބަބުން މިއަދު 12:00 ގައި ސްކޫލް ބަންދުކުރެވިއްޖެ. މާދަމާ އާދައިގެ ގަޑިތަކުގައި ކިޔެވުން އޮންނާނެ.',
                ],
            ],
        ];

        foreach ($rows as $row) {
            Announcement::create($row + ['is_published' => true]);
        }
    }

    private function seedNews(): void
    {
        $u = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1200&h=700&q=80";

        $rows = [
            [
                'slug' => 'regional-science-fair',
                'category' => 'achievements',
                'published_at' => '2026-06-20',
                'image' => $u('photo-1532094349884-543bc11b234d'),
                'author' => ['en' => 'Communications Office', 'dv' => 'ކޮމިއުނިކޭޝަން އޮފީސް'],
                'title' => [
                    'en' => 'Students Win Baa Atoll Science Fair',
                    'dv' => 'ދަރިވަރުން ބ. އަތޮޅު ސައިންސް ފެއަރ ކާމިޔާބުކޮށްފި',
                ],
                'excerpt' => [
                    'en' => 'Our Grade 9 team took first place with a solar-powered water purifier.',
                    'dv' => 'ގްރޭޑް 9 ޓީމު އިރުގެ ހަކަތައިން ފެން ސާފުކުރާ އާލާތަކުން ފުރަތަމަ ވަނަ ހޯދައިފި.',
                ],
                'body' => [
                    'en' => 'A team of four Grade 9 students earned first place at this year’s Baa Atoll Science Fair in Eydhafushi, impressing judges with a low-cost, solar-powered water purifier designed for island communities. The project began as a class experiment and grew into a working prototype over the term. Congratulations to the team and their mentors!',
                    'dv' => 'ގްރޭޑް 9 ގެ ހަތަރު ދަރިވަރުންގެ ޓީމަކުން މިއަހަރު އޭދަފުށީގައި ބޭއްވުނު ބ. އަތޮޅު ސައިންސް ފެއަރގައި ފުރަތަމަ ވަނަ ހޯދައިފި. ރަށްފުށުގެ މުޖްތަމަޢުތަކަށް އަމާޒުކޮށް، ޚަރަދު ކުޑަ، އިރުގެ ހަކަތައިން ފެން ސާފުކުރާ އާލާތެއް ފަރުމާކޮށްގެން. ޓީމަށާއި މުދައްރިސުންނަށް މަރުޙަބާ!',
                ],
            ],
            [
                'slug' => 'new-library-opens',
                'category' => 'schoolNews',
                'published_at' => '2026-05-30',
                'image' => $u('photo-1521587760476-6c12a4b040da'),
                'author' => ['en' => 'Aishath Rifga', 'dv' => 'ޢާއިޝަތު ރިފްޤާ'],
                'title' => [
                    'en' => 'New Library and Reading Garden Opens',
                    'dv' => 'އައު ލައިބްރަރީ އަދި ކިޔެވުމުގެ ބަގީޗާ ހުޅުވައިފި',
                ],
                'excerpt' => [
                    'en' => 'A bright new space with 2,000 books and a shaded outdoor reading garden.',
                    'dv' => '2,000 ފޮތާއެކު އަލިކަން ގަދަ އައު ޖާގައެއް އަދި ބޭރުގައި ކިޔެވުމުގެ ބަގީޗާއެއް.',
                ],
                'body' => [
                    'en' => 'We are delighted to open our newly renovated library, featuring 2,000 titles in Dhivehi and English, a digital catalogue, and a shaded outdoor reading garden. The space was made possible through the generosity of the Hithaadhoo community and parent volunteers.',
                    'dv' => 'ދިވެހި އަދި އިނގިރޭސި ބަހުން 2,000 ފޮތާއެކު، ޑިޖިޓަލް ކެޓަލޮގަކާއި، ބޭރުގައި ކިޔެވުމުގެ ބަގީޗާއަކާއެކު އައު ލައިބްރަރީ ހުޅުވުމަކީ އުފަލެއް. މިއީ ހިތާދޫގެ މުޖްތަމަޢުގެ ދީލަތި އެހީތެރިކަމާއެކު ޙާޞިލުވި ކަމެއް.',
                ],
            ],
            [
                'slug' => 'green-club-tree-planting',
                'category' => 'events',
                'published_at' => '2026-05-12',
                'image' => $u('photo-1542601906990-b4d3fb778b09'),
                'author' => ['en' => 'Environment Club', 'dv' => 'ތިމާވެށި ކްލަބް'],
                'title' => [
                    'en' => 'Environment Club Plants 200 Trees',
                    'dv' => 'ތިމާވެށި ކްލަބުން 200 ގަސް އިންދައިފި',
                ],
                'excerpt' => [
                    'en' => 'Students marked Environment Day by greening the school boundary.',
                    'dv' => 'ދަރިވަރުން ތިމާވެށީގެ ދުވަސް ފާހަގަކުރީ ސްކޫލްގެ ވަށައިގެން ގަސް އިންދައިގެން.',
                ],
                'body' => [
                    'en' => 'To mark World Environment Day, the Environment Club and volunteers planted 200 native trees and shrubs — dhiggaa, midhili and kaani — along the school boundary and the island’s coastal ridge, creating shade and protecting the shoreline. It’s part of our ongoing pledge to care for our island.',
                    'dv' => 'ދުނިޔޭގެ ތިމާވެށީގެ ދުވަސް ފާހަގަކުރުމަށް، ތިމާވެށި ކްލަބުން 200 ގަހާއި ގަސްގަހާގެހި ސްކޫލްގެ ވަށައިގެންނާއި ރަށުގެ ގޮންޑުދޮށުގައި އިންދައިފި. މިއީ ރަށަށް އަޅާލުމުގެ ވަޢުދުގެ ބައެއް.',
                ],
            ],
            [
                'slug' => 'annual-concert',
                'category' => 'events',
                'published_at' => '2026-04-28',
                'image' => $u('photo-1514320291840-2e0a9bf2a9ae'),
                'author' => ['en' => 'Arts Department', 'dv' => 'ފަންނުވެރިކަމުގެ ދާއިރާ'],
                'title' => [
                    'en' => 'Annual Cultural Concert Dazzles Families',
                    'dv' => 'އަހަރީ ސަޤާފީ ޝޯ އާއިލާތައް ހައިރާންކޮށްލައިފި',
                ],
                'excerpt' => [
                    'en' => 'An evening of Boduberu, drama and song celebrating island culture.',
                    'dv' => 'ބޮޑުބެރާއި، ޑްރާމާއާއި، ލަވައިން ފުރިގެންވި ސަޤާފީ ރެއެއް.',
                ],
                'body' => [
                    'en' => 'Over 300 students took to the stage for our Annual Cultural Concert, performing Boduberu, traditional drama and choral pieces to a full house. Thank you to every family who joined us to celebrate our island heritage.',
                    'dv' => '300 އަށްވުރެ ގިނަ ދަރިވަރުން އަހަރީ ސަޤާފީ ޝޯގައި ބައިވެރިވެ، ބޮޑުބެރާއި، ސަޤާފީ ޑްރާމާ ހުށަހަޅައިދިނެވެ. ބައިވެރިވެވަޑައިގެންނެވި ހުރިހާ އާއިލާތަކަށް ޝުކުރިއްޔާ.',
                ],
            ],
        ];

        foreach ($rows as $row) {
            NewsArticle::create($row + ['is_published' => true]);
        }
    }

    private function seedAcademicEvents(): void
    {
        $e = fn (int $id, string $date, ?string $endDate, int $term, string $type, string $en, string $dv) => array_filter([
            'id' => $id,
            'date' => $date,
            'endDate' => $endDate,
            'term' => $term,
            'type' => $type,
            'title' => ['en' => $en, 'dv' => $dv],
        ], fn ($value) => $value !== null);

        $events = [
            // 2025 — sample academic calendar
            $e(101, '2025-01-13', null, 1, 'term', 'Term 1 Begins', 'ފުރަތަމަ ޓާމް ފެށުން'),
            $e(102, '2025-02-10', '2025-02-14', 1, 'exam', 'Mid-Term Assessments', 'މިޑް-ޓާމް އިމްތިޙާން'),
            $e(103, '2025-03-03', null, 1, 'meeting', 'Parent–Teacher Meeting', 'ބެލެނިވެރިން-މުދައްރިސުން ބައްދަލުވުން'),
            $e(104, '2025-03-31', '2025-04-18', 1, 'holiday', 'Term 1 Break', 'ފުރަތަމަ ޓާމް ބަންދު'),
            $e(105, '2025-04-21', null, 2, 'term', 'Term 2 Begins', 'ދެވަނަ ޓާމް ފެށުން'),
            $e(106, '2025-05-19', '2025-05-23', 2, 'exam', 'Term 2 Examinations', 'ދެވަނަ ޓާމް އިމްތިޙާން'),
            $e(107, '2025-06-26', '2025-08-03', 2, 'holiday', 'Mid-Year Holiday', 'މެދު-އަހަރު ބަންދު'),
            $e(108, '2025-07-26', null, 2, 'event', 'Independence Day', 'މިނިވަން ދުވަސް'),
            $e(109, '2025-08-04', null, 3, 'term', 'Term 3 Begins', 'ތިންވަނަ ޓާމް ފެށުން'),
            $e(110, '2025-08-09', null, 3, 'event', 'Annual Sports Day', 'އަހަރީ ކުޅިވަރު ދުވަސް'),
            $e(111, '2025-10-06', null, 3, 'meeting', 'Professional Development Day', 'ޕްރޮފެޝަނަލް ޑިވެލޮޕްމަންޓް ދުވަސް'),
            $e(112, '2025-11-03', null, 3, 'event', 'Victory Day', 'ނަޞްރުގެ ދުވަސް'),
            $e(113, '2025-11-17', '2025-11-28', 3, 'exam', 'Final Examinations', 'ފައިނަލް އިމްތިޙާން'),
            $e(114, '2025-12-11', null, 3, 'term', 'End of Term 3', 'ތިންވަނަ ޓާމް ނިމުން'),
            $e(115, '2025-12-12', '2026-01-11', 3, 'holiday', 'Year-End Holidays', 'އަހަރު ނިމުމުގެ ބަންދު'),

            // 2026 — Ministry of Education academic calendar
            $e(1, '2026-01-01', null, 1, 'event', 'New Year 2026', 'އައު އަހަރު 2026'),
            $e(2, '2026-01-25', null, 1, 'meeting', "Teachers' Reporting Day 2026", 'މުދައްރިސުން ހާޒިރުވާ ދުވަސް 2026'),
            $e(3, '2026-01-27', null, 1, 'term', 'Beginning of Academic Year 2026', 'ދިރާސީ އަހަރު ފެށުން 2026'),
            $e(4, '2026-02-05', null, 1, 'event', "Opening of the People's Majlis", 'ރައްޔިތުންގެ މަޖިލިސް ހުޅުވުން'),
            $e(5, '2026-02-18', null, 1, 'event', 'First of Ramadan', 'ރަމަޟާން މަހުގެ ފުރަތަމަ ދުވަސް'),
            $e(6, '2026-03-01', '2026-03-08', 1, 'meeting', 'Professional Development Days', 'ޕްރޮފެޝަނަލް ޑިވެލޮޕްމަންޓް ދުވަސްތައް'),
            $e(7, '2026-03-09', '2026-03-19', 1, 'holiday', 'Last 10 days of Ramadan', 'ރަމަޟާން މަހުގެ ފަހު ދިހަ'),
            $e(8, '2026-03-20', null, 1, 'event', 'Eid-al-Fitr', 'ފިޠުރު ޢީދު'),
            $e(9, '2026-03-21', '2026-03-22', 1, 'event', 'On the occasion of Eid-al-Fitr', 'ފިޠުރު ޢީދާ ގުޅިގެން'),
            $e(10, '2026-04-05', '2026-04-14', 1, 'exam', 'Grade 11 and 12 First Term Exam', 'ގްރޭޑް 11 އަދި 12 ފުރަތަމަ ޓާމް އިމްތިޙާން'),
            $e(11, '2026-05-01', null, 1, 'event', 'Labour Day', 'މަސައްކަތްތެރިންގެ ދުވަސް'),
            $e(12, '2026-05-10', null, 1, 'event', "Children's Day", 'ކުޑަކުދިންގެ ދުވަސް'),
            $e(13, '2026-05-17', '2026-06-15', 1, 'meeting', 'School Transfer Period 1', 'ސްކޫލް ބަދަލުކުރުމުގެ މުއްދަތު 1'),
            $e(14, '2026-05-24', '2026-05-30', 1, 'holiday', 'First Term Mid-Break', 'ފުރަތަމަ ޓާމް މެދު ބަންދު'),
            $e(15, '2026-05-26', null, 1, 'event', 'Hajj Day', 'ޙައްޖު ދުވަސް'),
            $e(16, '2026-05-27', null, 1, 'event', 'Eid-al-Adha', 'އަޟްޙާ ޢީދު'),
            $e(17, '2026-05-28', '2026-05-30', 1, 'event', 'On the occasion of Eid-al-Adha', 'އަޟްޙާ ޢީދާ ގުޅިގެން'),
            $e(18, '2026-06-07', null, 1, 'term', 'Beginning of AL Batch 2026', 'އޭ.އެލް ބެޗް 2026 ފެށުން'),
            $e(19, '2026-06-16', null, 1, 'event', 'Islamic New Year 1448', 'އިސްލާމީ އައު އަހަރު 1448'),
            $e(20, '2026-06-30', '2026-07-09', 1, 'exam', 'First Term Exam', 'ފުރަތަމަ ޓާމް އިމްތިޙާން'),
            $e(21, '2026-07-09', '2026-08-31', 1, 'meeting', 'New Admission - LKG & Gr. 1', 'އައު އެޑްމިޝަން - އެލްކޭޖީ އަދި ގްރޭޑް 1'),
            $e(22, '2026-07-16', null, 1, 'term', 'End of First Term 2026', 'ފުރަތަމަ ޓާމް ނިމުން 2026'),
            $e(23, '2026-07-17', '2026-08-01', 1, 'holiday', 'First Term Holidays', 'ފުރަތަމަ ޓާމް ބަންދު'),
            $e(24, '2026-07-26', null, 1, 'event', 'Independence Day', 'މިނިވަން ދުވަސް'),
            $e(25, '2026-07-27', null, 1, 'event', 'On the occasion of Independence Day', 'މިނިވަން ދުވަހާ ގުޅިގެން'),
            $e(26, '2026-08-02', null, 2, 'term', 'Beginning of Second Term 2026', 'ދެވަނަ ޓާމް ފެށުން 2026'),
            $e(27, '2026-08-14', null, 2, 'event', 'National Day', 'ޤައުމީ ދުވަސް'),
            $e(28, '2026-08-22', '2026-08-25', 2, 'meeting', 'Camps and Activities (KS1 - KS4)', 'ކޭމްޕް އަދި ހަރަކާތްތައް (KS1 - KS4)'),
            $e(29, '2026-08-23', '2026-09-10', 2, 'exam', 'Grade 11 and 12 Final Exam', 'ގްރޭޑް 11 އަދި 12 ފައިނަލް އިމްތިޙާން'),
            $e(30, '2026-08-25', null, 2, 'event', "Prophet Muhammad's (ﷺ) Birthday", 'ރަސޫލާ ﷺ ގެ އީދު މީލާދު'),
            $e(31, '2026-09-01', '2026-09-10', 2, 'exam', 'Grade 10 Mock Exam', 'ގްރޭޑް 10 މޮކް އިމްތިޙާން'),
            $e(32, '2026-09-13', null, 2, 'event', 'The Day Maldives Embraced Islam', 'ދިވެހިރާއްޖެ އިސްލާމްވި ދުވަސް'),
            $e(33, '2026-09-13', '2026-09-19', 2, 'holiday', 'Second Term Mid-Break', 'ދެވަނަ ޓާމް މެދު ބަންދު'),
            $e(34, '2026-10-05', null, 2, 'event', "Teachers' Day", 'މުދައްރިސުންގެ ދުވަސް'),
            $e(35, '2026-10-18', '2026-11-17', 2, 'meeting', 'School Transfer Period 2', 'ސްކޫލް ބަދަލުކުރުމުގެ މުއްދަތު 2'),
            $e(36, '2026-11-03', null, 2, 'event', 'Victory Day', 'ނަޞްރުގެ ދުވަސް'),
            $e(37, '2026-11-11', null, 2, 'event', 'Republic Day', 'ޖުމްހޫރީ ދުވަސް'),
            $e(38, '2026-11-12', null, 2, 'meeting', 'Professional Development Day', 'ޕްރޮފެޝަނަލް ޑިވެލޮޕްމަންޓް ދުވަސް'),
            $e(39, '2026-12-01', '2026-12-10', 2, 'exam', 'Second Term Exam', 'ދެވަނަ ޓާމް އިމްތިޙާން'),
            $e(40, '2026-12-01', '2026-12-10', 2, 'exam', 'First Term Exam (Gr. 11, 2026 Batch)', 'ފުރަތަމަ ޓާމް އިމްތިޙާން (ގްރޭޑް 11، 2026 ބެޗް)'),
            $e(41, '2026-12-17', null, 2, 'term', 'End of Second Term 2026', 'ދެވަނަ ޓާމް ނިމުން 2026'),
            $e(42, '2026-12-18', '2027-01-12', 2, 'holiday', 'Second Term Holidays', 'ދެވަނަ ޓާމް ބަންދު'),
        ];

        SiteContent::create(['key' => 'academic_events', 'value' => $events]);
    }
}
