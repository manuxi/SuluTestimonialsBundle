<?php

declare(strict_types=1);

namespace Manuxi\SuluTestimonialsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Manuxi\SuluTestimonialsBundle\Entity\Testimonial;
use Manuxi\SuluTestimonialsBundle\Entity\TestimonialDimensionContent;
use Sulu\Bundle\ContactBundle\Entity\Contact;

class TestimonialFixtures extends Fixture implements FixtureGroupInterface
{
    private const TESTIMONIALS = [
        [
            'de' => [
                'title' => 'Herausragender Service',
                'text' => '<p>Das Team hat unsere Erwartungen übertroffen. Die Liebe zum Detail und das Engagement für Qualität sind bemerkenswert. Wir verzeichneten eine 40% höhere Kundenzufriedenheit nach der Implementierung.</p>',
            ],
            'en' => [
                'title' => 'Outstanding Service Experience',
                'text' => '<p>The team exceeded all our expectations. Their attention to detail and commitment to quality is remarkable. We saw a 40% increase in customer satisfaction.</p>',
            ],
            'rating' => '10',
            'source' => 'Google Reviews',
            'website' => 'https://google.com/reviews',
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Sehr empfehlenswerter Partner',
                'text' => '<p>Die Zusammenarbeit mit diesem Unternehmen war ein Gamechanger für unser Geschäft. Ihre Expertise und Professionalität sind branchenweit unübertroffen.</p>',
            ],
            'en' => [
                'title' => 'Highly Recommended Partner',
                'text' => '<p>Working with this company has been a game-changer for our business. Their expertise and professionalism are unmatched in the industry.</p>',
            ],
            'rating' => '9',
            'source' => 'LinkedIn',
            'website' => 'https://linkedin.com',
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Außergewöhnliche Qualität',
                'text' => '<p>Von Anfang bis Ende war die Erfahrung nahtlos. Die Produktqualität ist ausgezeichnet und das Support-Team ist immer erreichbar.</p>',
            ],
            'en' => [
                'title' => 'Exceptional Quality',
                'text' => '<p>From start to finish, the experience was seamless. The product quality is excellent and the support team is always available when needed.</p>',
            ],
            'rating' => '10',
            'source' => 'Trustpilot',
            'website' => 'https://trustpilot.com',
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Tolles Preis-Leistungs-Verhältnis',
                'text' => '<p>Wir haben mehrere Anbieter verglichen und dieser bot die beste Kombination aus Funktionen und Preis. Jeden Cent wert.</p>',
            ],
            'en' => [
                'title' => 'Great Value for Money',
                'text' => '<p>We compared several providers and this one offered the best combination of features and pricing. Definitely worth every penny.</p>',
            ],
            'rating' => '8',
            'source' => 'G2 Crowd',
            'website' => 'https://g2.com',
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Hat unseren Workflow transformiert',
                'text' => '<p>Die Implementierung verlief reibungslos und die Ergebnisse waren sofort sichtbar. Unsere Teamproduktivität stieg im ersten Monat um 25%.</p>',
            ],
            'en' => [
                'title' => 'Transformed Our Workflow',
                'text' => '<p>Implementation was smooth and the results were immediate. Our team productivity increased by 25% within the first month.</p>',
            ],
            'rating' => '9',
            'source' => 'Capterra',
            'website' => 'https://capterra.com',
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Zuverlässig und vertrauenswürdig',
                'text' => '<p>Nach drei Jahren Partnerschaft kann ich selbstbewusst sagen: Dies ist ein Unternehmen, auf das man sich verlassen kann. Sie liefern, was sie versprechen.</p>',
            ],
            'en' => [
                'title' => 'Reliable and Trustworthy',
                'text' => '<p>After three years of partnership, I can confidently say this is a company you can rely on. They deliver what they promise, every time.</p>',
            ],
            'rating' => '10',
            'source' => 'Personal Reference',
            'website' => null,
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Innovative Lösungen',
                'text' => '<p>Ihr Ansatz zur Problemlösung ist erfrischend. Sie brachten innovative Ideen ein, an die wir noch gar nicht gedacht hatten.</p>',
            ],
            'en' => [
                'title' => 'Innovative Solutions',
                'text' => '<p>Their approach to problem-solving is refreshing. They brought innovative ideas that we hadn\'t even considered.</p>',
            ],
            'rating' => '8',
            'source' => 'Industry Conference',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Professionelles Team',
                'text' => '<p>Jede Interaktion mit dem Team war professionell und zuvorkommend. Sie kümmern sich wirklich um ihre Kunden.</p>',
            ],
            'en' => [
                'title' => 'Professional Team',
                'text' => '<p>Every interaction with their team has been professional and courteous. They truly care about their customers.</p>',
            ],
            'rating' => '9',
            'source' => 'Email Survey',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Erwartungen übertroffen',
                'text' => '<p>Wir hatten hohe Erwartungen und sie haben es geschafft, diese noch zu übertreffen. Das Endergebnis war besser als wir uns vorgestellt hatten.</p>',
            ],
            'en' => [
                'title' => 'Exceeded Expectations',
                'text' => '<p>We had high expectations and they still managed to exceed them. The final result was better than we imagined.</p>',
            ],
            'rating' => '10',
            'source' => 'Facebook',
            'website' => 'https://facebook.com',
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Solide Leistung',
                'text' => '<p>Das System läuft seit über einem Jahr einwandfrei. Die Verfügbarkeit ist ausgezeichnet und die Leistung konstant.</p>',
            ],
            'en' => [
                'title' => 'Solid Performance',
                'text' => '<p>The system has been running flawlessly for over a year now. Uptime is excellent and performance is consistent.</p>',
            ],
            'rating' => '8',
            'source' => 'Technical Review',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Kunde steht an erster Stelle',
                'text' => '<p>Was sie auszeichnet, ist ihr echter Fokus auf den Kundenerfolg. Sie gehen über das hinaus, was erwartet wird.</p>',
            ],
            'en' => [
                'title' => 'Customer First Approach',
                'text' => '<p>What sets them apart is their genuine focus on customer success. They go above and beyond to ensure satisfaction.</p>',
            ],
            'rating' => '9',
            'source' => 'Yelp',
            'website' => 'https://yelp.com',
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Nahtlose Integration',
                'text' => '<p>Die Integration in unsere bestehenden Systeme war problemlos. Die Dokumentation ist ausgezeichnet und der Support war immer verfügbar.</p>',
            ],
            'en' => [
                'title' => 'Seamless Integration',
                'text' => '<p>Integration with our existing systems was painless. The documentation is excellent and support was always available.</p>',
            ],
            'rating' => '9',
            'source' => 'Stack Overflow',
            'website' => 'https://stackoverflow.com',
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Beste Entscheidung des Jahres',
                'text' => '<p>Die Wahl dieser Lösung war die beste Geschäftsentscheidung, die wir dieses Jahr getroffen haben. Der ROI war innerhalb von Wochen sichtbar.</p>',
            ],
            'en' => [
                'title' => 'Best Decision We Made',
                'text' => '<p>Choosing this solution was the best business decision we made this year. ROI was visible within weeks.</p>',
            ],
            'rating' => '10',
            'source' => 'Direct Feedback',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Beeindruckender Support',
                'text' => '<p>Der technische Support ist hervorragend. Die Reaktionszeiten sind schnell und das Team ist kompetent und hilfsbereit.</p>',
            ],
            'en' => [
                'title' => 'Impressive Support',
                'text' => '<p>Technical support is outstanding. Response times are fast and the team is knowledgeable and helpful.</p>',
            ],
            'rating' => '9',
            'source' => 'Support Survey',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Qualität, der man vertrauen kann',
                'text' => '<p>In einer Branche voller leerer Versprechen liefert dieses Unternehmen echte Ergebnisse. Qualität und Zuverlässigkeit garantiert.</p>',
            ],
            'en' => [
                'title' => 'Quality You Can Trust',
                'text' => '<p>In an industry full of empty promises, this company delivers real results. Quality and reliability guaranteed.</p>',
            ],
            'rating' => '10',
            'source' => 'Industry Award',
            'website' => null,
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Gut, aber mit Verbesserungspotential',
                'text' => '<p>Insgesamt eine solide Lösung. Es gibt noch Raum für Verbesserungen, aber das Team arbeitet kontinuierlich daran.</p>',
            ],
            'en' => [
                'title' => 'Good but Room for Improvement',
                'text' => '<p>Overall a solid solution. There is still room for improvement, but the team is continuously working on it.</p>',
            ],
            'rating' => '7',
            'source' => 'Customer Survey',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Hervorragende Zusammenarbeit',
                'text' => '<p>Die Zusammenarbeit war von Anfang an konstruktiv und professionell. Sehr zu empfehlen!</p>',
            ],
            'en' => [
                'title' => 'Excellent Collaboration',
                'text' => '<p>The collaboration was constructive and professional from the start. Highly recommended!</p>',
            ],
            'rating' => '10',
            'source' => 'Project Completion',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Zuverlässiger Geschäftspartner',
                'text' => '<p>Seit zwei Jahren arbeiten wir zusammen und wurden nie enttäuscht. Ein Partner, auf den man sich verlassen kann.</p>',
            ],
            'en' => [
                'title' => 'Reliable Business Partner',
                'text' => '<p>We have been working together for two years and have never been disappointed. A partner you can rely on.</p>',
            ],
            'rating' => '9',
            'source' => 'Business Partner',
            'website' => null,
            'published' => false,
        ],
        [
            'de' => [
                'title' => 'Erstklassige Qualität',
                'text' => '<p>Die Qualität der Arbeit ist erstklassig. Jedes Detail wird beachtet und nichts dem Zufall überlassen.</p>',
            ],
            'en' => [
                'title' => 'First-Class Quality',
                'text' => '<p>The quality of work is first-class. Every detail is considered and nothing is left to chance.</p>',
            ],
            'rating' => '10',
            'source' => 'Quality Review',
            'website' => null,
            'published' => true,
        ],
        [
            'de' => [
                'title' => 'Rundum zufrieden',
                'text' => '<p>Wir sind rundum zufrieden mit dem Service. Schnelle Reaktionszeiten und kompetente Beratung.</p>',
            ],
            'en' => [
                'title' => 'Completely Satisfied',
                'text' => '<p>We are completely satisfied with the service. Fast response times and competent advice.</p>',
            ],
            'rating' => '8',
            'source' => 'Customer Feedback',
            'website' => null,
            'published' => true,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $contacts = $manager->getRepository(Contact::class)->findAll();

        if (empty($contacts)) {
            echo "Warning: No contacts found. Testimonials will be created without contact references.\n";
        }

        $now = new \DateTimeImmutable();
        $publishedCount = 0;
        $draftOnlyCount = 0;

        foreach (self::TESTIMONIALS as $index => $data) {
            $testimonial = new Testimonial();
            $manager->persist($testimonial);
            $manager->flush();

            $contact = !empty($contacts) ? $contacts[$index % count($contacts)] : null;
            $isPublished = $data['published'];
            $date = $now->modify(sprintf('-%d days', rand(1, 365)));

            foreach (['de', 'en'] as $locale) {
                $localeData = $data[$locale];

                $draftContent = $this->createDimensionContent(
                    $testimonial,
                    $locale,
                    'draft',
                    $localeData['title'],
                    $localeData['text'],
                    $data['rating'],
                    $data['source'],
                    $data['website'],
                    $contact,
                    $date,
                    $isPublished
                );
                $manager->persist($draftContent);

                if ($isPublished) {
                    $liveContent = $this->createDimensionContent(
                        $testimonial,
                        $locale,
                        'live',
                        $localeData['title'],
                        $localeData['text'],
                        $data['rating'],
                        $data['source'],
                        $data['website'],
                        $contact,
                        $date,
                        true
                    );
                    $manager->persist($liveContent);
                }
            }

            if ($isPublished) {
                $publishedCount++;
            } else {
                $draftOnlyCount++;
            }
        }

        $manager->flush();

        echo sprintf(
            "Created %d testimonials (%d published, %d draft-only)\n",
            count(self::TESTIMONIALS),
            $publishedCount,
            $draftOnlyCount
        );
    }

    private function createDimensionContent(
        Testimonial $testimonial,
        string $locale,
        string $stage,
        string $title,
        string $text,
        string $rating,
        string $source,
        ?string $website,
        ?Contact $contact,
        \DateTimeImmutable $date,
        bool $isPublished
    ): TestimonialDimensionContent {
        $content = new TestimonialDimensionContent($testimonial);

        $reflection = new \ReflectionClass($content);

        $this->setProperty($reflection, $content, 'locale', $locale);
        $this->setProperty($reflection, $content, 'stage', $stage);
        $this->setProperty($reflection, $content, 'version', 0);

        $content->setTitle($title);
        $content->setText($text);
        $content->setRating($rating);
        $content->setSource($source);
        $content->setWebsite($website);
        $content->setDate($date);

        if ($contact !== null) {
            $content->setContact($contact);
        }

        $content->setShowContact(true);
        $content->setShowDate(rand(0, 1) === 1);
        $content->setShowOrganisation(rand(0, 1) === 1);

        if ($isPublished && $stage === 'draft') {
            $this->setProperty($reflection, $content, 'workflowPlace', 'published');
        } elseif ($stage === 'live') {
            $this->setProperty($reflection, $content, 'workflowPlace', 'published');
            $this->setProperty($reflection, $content, 'workflowPublished', $date);
        } else {
            $this->setProperty($reflection, $content, 'workflowPlace', 'unpublished');
        }

        return $content;
    }

    private function setProperty(\ReflectionClass $reflection, object $object, string $property, mixed $value): void
    {
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public static function getGroups(): array
    {
        return ['testimonial', 'testimonials'];
    }
}