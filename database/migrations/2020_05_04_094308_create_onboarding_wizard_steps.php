<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
class CreateOnboardingWizardSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('onboarding_wizard_steps');
        Schema::create('onboarding_wizard_steps', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->char('onboarding_wizard_id', 36);
            $table->char('parent_id')->nullable();
            $table->string('title');
            $table->string('action')->nullable();
            $table->text('action_content')->nullable();
            $table->text('knowledge_base_action')->nullable();
            $table->string('confetti_max_count')->nullable();
            $table->string('confetti_time_out')->nullable();
            $table->integer('displayorder')->default(1);
        });

        if(Schema::hasTable('onboarding_wizard_user_states')) {
            \tcCore\OnboardingWizardUserState::query()->truncate();
        }
        if(Schema::hasTable('onboarding_wizard_user_steps')) {
            \tcCore\OnboardingWizardUserStep::query()->truncate();
        }
        if(Schema::hasTable('onboarding_wizards')) {
            OnboardingWizard::query()->truncate();
        }

        if (!Schema::hasColumn('onboarding_wizards', 'uuid')) {
            Schema::table('onboarding_wizards', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });
        }

        if (!Schema::hasColumn('onboarding_wizard_steps', 'uuid')) {
            Schema::table('onboarding_wizard_steps', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });
        }

        self::addData();
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboarding_wizard_steps');
    }
    public static function addData()
    {
        $wizardId = Str::uuid();
        OnboardingWizard::create([
            'id'      => $wizardId,
            'title'   => 'Test Wizard',
            'role_id' => 1,
            'active'  => true,
        ]);

        $arr = collect([
            [
                'title' => 'Vogelvlucht Test-Correct <small>(10 minuten)</small>',
                'sub'   => [
                    [
                        'title' => 'Test-Correct in vogelvlucht.',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/Wy5MFtTeCZ0',
                        'knowledge_base_action' => config('app.knowledge_bank_url'),
                    ],
                    [
                        'title'          => 'Klik zelf door Test-Correct.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/ymdrX14eOIg',
                    ],
                    [
                        'title'          => 'We hebben een demotoets voor je klaargezet!',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/0fhZg050CtY',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/itembank',
                    ],
                    [
                        'title'          => 'Bekijk zelf de demotoets.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/WXsFFavzuVC',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Gefeliciteerd! Je hebt nu de basis gezien van <br/>Test-Correct. Ga zo door!',
                        'confetti_max_count' => '200',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'Toets inplannen voor afname en surveilleren <small>(10 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Hoe werkt het inplannen van een toets?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/NbosbKEbDm4',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/inplannen',
                    ],
                    [
                        'title'          => 'Plan zelf een toets in',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/aa8ajkEaio7',
                    ],
                    [
                        'title'          => 'Hoe kun je een toets afnemen en surveilleren?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/aNYg5MxnKpQ',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/surveilleren-van-toetsen',
                    ],
                    [
                        'title'          => 'Je eerste toets afnemen en surveilleren.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/2bFE2XafNtn',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Goed gedaan!',
                        'confetti_max_count' => '300',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'Nakijken, normeren en becijferen <small>(15 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Hoe kijk je een toets na in Test-Correct?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/fTe8E3MT5Rc',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/toets-nakijken',
                    ],
                    [
                        'title'          => 'Kijk je eerste toets na.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/jGhhAxEDnof',
                    ],
                    [
                        'title'          => 'Hoe normeer en becijfer je toetsen in Test-Correct?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/JooBFbN6jDQ',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/normeren',
                    ],
                    [
                        'title'          => 'Normeer en becijfer zelf een toets.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/xRrN1sUDTPn',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Gefeliciteerd! Je kunt nu aan de slag met <br/>Test-Correct!',
                        'confetti_max_count' => '1000',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'CO-Learning <small>(5 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Wat is CO-Learning?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/IE8pfZz5ZqM',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/co-learning-module',
                    ],
                    [
                        'title'          => 'Zelf aan de slag met CO-Learning.',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/bSaPBNnVloQ',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Zo eenvoudig kan lesgeven zijn!',
                        'confetti_max_count' => '500',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'Analyses <small>(2 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Hoe houd je zicht op de voortgang van je studenten?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/-S2utWIORoo',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/analyse-1',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Je weet nu hoe je inzicht krijgt in de sterktes en zwaktes van je studenten!',
                        'confetti_max_count' => '600',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'Uw klassen en bestaande toetsen uploaden <small>(5 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Hoe kun je je klassen uploaden?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/Y_yi0H4vGlA',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/klas-uploaden',
                    ],
                    [
                        'title'          => 'Hoe kun je je bestaande toetsen uploaden?',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/-AQyzBffjKs',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/toets-uploaden',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Je bent bijna klaar met de demo tour. Ga zo door!',
                        'confetti_max_count' => '700',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],

            [
                'title' => 'Zelf toetsen construeren <small>(20 minuten)</small>',
                'sub'   => [
                    [
                        'title'          => 'Hoe kun je zelf toetsen construeren? (deel 1)',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/lh7gWGZ4pzE',
                        'knowledge_base_action' => config('app.knowledge_bank_url').'/starten-met-toets-construeren',
                    ],
                    [
                        'title'          => 'Hoe kun je zelf toetsen construeren? (deel 2)',
                        'action'         => OnboardingWizard::VIDEO,
                        'action_content' => 'https://www.youtube.com/embed/rs2rGtiEOEI',
                    ],
                    [
                        'title'          => 'Construeer zelf een toets.',
                        'action'         => OnboardingWizard::TOUR,
                        'action_content' => 'https://hlp.sh/t/2jSC4R/HneXdQ7m2Hd',
                    ],
                    [
                        'title'          => 'Klik hier als je alle stappen hebt gedaan',
                        'action'         => OnboardingWizard::BUTTON_DONE,
                        'action_content' => 'Gefeliciteerd! Je hebt de demo tour afgerond. Niks staat je meer in de weg om Test-Correct in te zetten in je klassen.',
                        'confetti_max_count' => '1200',
                        'confetti_time_out' => '3000',
                    ],
                ],
            ],
        ]);
        return $arr->map(function ($step, $index) use ($wizardId) {
            $parentStep = self::createStep([
                'title'                => $step['title'],
                'onboarding_wizard_id' => $wizardId,
                'displayorder'         => ++$index,
            ]);
            collect($step['sub'])->map(function ($step, $index) use ($parentStep, $wizardId) {
                return self::createStep([
                    'title'                 => $step['title'],
                    'parent_id'             => $parentStep->getKey(),
                    'displayorder'          => ++$index,
                    'onboarding_wizard_id'  => $wizardId,
                    'action'                => $step['action'] ?? null,
                    'action_content'        => $step['action_content'] ?? null,
                    'confetti_time_out'     => $step['confetti_time_out'] ?? null,
                    'confetti_max_count'    => $step['confetti_max_count'] ?? null,
                    'knowledge_base_action' => $step['knowledge_base_action'] ?? null,
                ]);
            });
            return $step;
        });
    }

    private static function createStep($overrides = [])
    {
        $attributes = array_merge([
            'id'           => Str::uuid(),
            'title'        => 'stap',
            'displayorder' => 1
        ], $overrides);
        return OnboardingWizardStep::create($attributes);
    }
}
