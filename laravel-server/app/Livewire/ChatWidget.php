<?php

namespace App\Livewire;

use Livewire\Component;
use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;
use App\Models\Technology;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ChatWidget extends Component
{
    public $panelHidden = true;
    public $winPosition = 'right';
    public $winWidth = 'width: 400px;';
    public $showPositionBtn = true;
    public $name = 'KMC Chatbot';
    public $messages = [];
    public $question = '';
    public $showCreateButton = false;

    protected $predefinedInstructions = "You are an AI assistant called KMC Chatbot for a job recruitment platform. Your task is to help create project requirements and overview, answer questions about the recruitment process, and provide information about various job roles and industries. Please ensure the output is **well-structured** with correct **spacing**, **bulleted lists**, and **bold text**. Maintain a professional tone. If information is missing, such as the location, omit it gracefully without altering the structure. Follow these formatting guidelines strictly.

---

### **Gemini Task Instructions:**
1. Use **bold** text for key section headers and field labels (e.g., Project Title, Budget, Location, Timeline, Technologies).
2. Use **one line of space** between paragraphs and bullet points to ensure readability.
3. Ensure **bulleted lists are properly aligned** with no extra or missing spaces.
4. If a section has no data, omit it gracefully without changing the structure.
5. Maintain the **motivational tone** in the closing sections.
6. Again, use line breaks per bullet and section headers and paragraphs.
7. If you have been tasked to create a project overview, strictly follow the template provided.

---

Here is the template:

---

**Project Title:**  {projectTitle}

**Client Name:**  {clientName}

**Project Duration:**  {startDate} - {endDate}

**Budget:**  {₱XX,XXX.XX}

**Location:**  {location}

**Deadline:**  {mm/dd/yyyy}

**Technologies:** {technologies/tools[]}

**Project Overview:**
[Briefly describe the project's purpose, objectives, and the problem it aims to solve.]

**Goals and Objectives:**
- [List primary goals and objectives of the project.]
- [Include measurable outcomes if applicable.]

**Scope of Work:**
- **Services Provided:**  [Detail the specific services KMC Solutions will offer.]
- **Technologies:**  {technologies/tools[]}
- **Deliverables:**  [Enumerate the expected deliverables for the project.]

**Team Composition:**
- [Outline the roles and expertise of the team members involved in the project.]

**Project Approach:**
- **Methodology:**  [Describe the methodology (e.g., Agile, Waterfall) used for project execution.]
- **Phases:**  [Briefly outline the phases of the project, including planning, execution, and review.]

**Expected Outcomes:**
[Describe the anticipated results, benefits to the client, and overall impact on the IT industry.]

**Conclusion:**
[Summarize the importance of the project and KMC Solutions' commitment to delivering exceptional results.]

";

    protected function getClient()
    {
        return new Client(env('GEMINI_API_KEY'));
    }

    public function mount()
    {
        $this->messages = [
            ['role' => 'system', 'content' => $this->predefinedInstructions]
        ];
    }

    public function togglePanel()
    {
        $this->panelHidden = !$this->panelHidden;
    }

    public function changeWinWidth()
    {
        $this->winWidth = $this->winWidth === 'width: 100%;' ? 'width: 400px;' : 'width: 100%;';
    }

    public function changeWinPosition()
    {
        $this->winPosition = $this->winPosition === 'left' ? 'right' : 'left';
    }

    public function resetSession()
    {
        $this->messages = [
            ['role' => 'system', 'content' => $this->predefinedInstructions]
        ];
        $this->showCreateButton = false;
    }

    public function extractProjectDetails()
    {
        $lastMessage = collect($this->messages)
            ->reverse()
            ->firstWhere('role', 'assistant');

        if (!$lastMessage) {
            return;
        }

        $content = $lastMessage['content'];

        // Extract project details
        $details = [
            'title' => $this->extractValue($content, 'Project Title:\s*(.+)(?=\n)'),
            'budget' => $this->extractBudget($content),
            'location' => $this->extractValue($content, 'Location:\s*(.+)(?=\n)'),
            'timeline' => $this->extractTimeline($content),
            'technologies' => $this->extractTechnologies($content),
            'description' => $this->extractDescription($content)
        ];

        // Get or create technology IDs
        $technologyIds = [];
        foreach ($details['technologies'] as $techName) {
            if (!empty($techName)) {
                $technology = Technology::firstOrCreate(['name' => trim($techName)]);
                $technologyIds[] = $technology->id;
            }
        }
        $details['technology_ids'] = $technologyIds;

        // Log the extracted details for debugging
        Log::info('Extracted project details:', $details);

        // Dispatch the event with the extracted data
        $this->dispatch('fill-project-form', details: $details);

        // Show a success notification
        Notification::make()
            ->title('Project details extracted')
            ->success()
            ->send();
    }

    protected function extractValue($content, $pattern)
    {
        if (preg_match('/' . $pattern . '/i', $content, $matches)) {
            // Remove leading/trailing whitespace and asterisks
            return trim(preg_replace('/^\*{2}\s*|\s*\*{2}$/', '', trim($matches[1])));
        }
        return null;
    }

    protected function extractBudget($content)
    {
        // Try both formats: with and without asterisks
        $patterns = [
            '/\*\*Budget:\*\*\s*₱([0-9,.]+)/i',  // Format with asterisks
            '/Budget:\s*₱([0-9,.]+)/i',           // Format without asterisks
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                // Remove commas and convert to float
                return (float) str_replace(',', '', trim($matches[1]));
            }
        }

        return null;
    }

    protected function extractTimeline($content)
    {
        // Try multiple patterns and formats
        $patterns = [
            '/\*\*Deadline:\*\*\s*(\d{2}\/\d{2}\/\d{4})/i',    // Format: **Deadline:** mm/dd/yyyy
            '/Deadline:\s*(\d{2}\/\d{2}\/\d{4})/i',            // Format: Deadline: mm/dd/yyyy
            '/\*\*Timeline:\*\*\s*(\w+\s+\d{1,2},?\s*\d{4})/i', // Format: **Timeline:** June 30, 2024
            '/Timeline:\s*(\w+\s+\d{1,2},?\s*\d{4})/i',         // Format: Timeline: June 30, 2024
            '/\*\*Timeline:\*\*\s*(\d{1,2}\/\d{1,2}\/\d{4})/i', // Format: **Timeline:** dd/mm/yyyy
            '/Timeline:\s*(\d{1,2}\/\d{1,2}\/\d{4})/i',         // Format: Timeline: dd/mm/yyyy
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                try {
                    $dateStr = trim($matches[1]);

                    // Handle different date formats
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateStr)) {
                        // If it's already in mm/dd/yyyy format
                        $date = Carbon::createFromFormat('m/d/Y', $dateStr);
                    } else {
                        // If it's in text format (e.g., "June 30, 2024")
                        $date = Carbon::parse($dateStr);
                    }

                    // Return in the format expected by Filament's DatePicker (Y-m-d)
                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::error('Error parsing date: ' . $e->getMessage());
                }
            }
        }

        // If no timeline found, try to find a date in the description
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $content, $matches)) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $matches[1]);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Error parsing fallback date: ' . $e->getMessage());
            }
        }

        return null;
    }

    protected function extractTechnologies($content)
    {
        $technologies = [];

        // Try to find technologies section
        if (preg_match('/Technologies:(.+?)(?=\n\n|$)/s', $content, $matches)) {
            $techSection = $matches[1];

            // Split by newlines and cleanup
            $technologies = array_map('trim', explode("\n", $techSection));
            $technologies = array_filter($technologies); // Remove empty items

            // Remove any bullet points or dashes
            $technologies = array_map(function ($tech) {
                return trim($tech, "- *•\t");
            }, $technologies);
        }

        return array_filter($technologies);
    }

    protected function extractDescription($content)
    {
        if (
            preg_match('/\*\*Project Overview:\*\*.*$/s', $content, $matches) ||
            preg_match('/Project Overview:.*$/s', $content, $matches)
        ) {

            $description = $matches[0];

            // First, convert specific markdown headers to HTML
            $description = preg_replace('/\*\*(.*?):\*\*/', '<strong>$1:</strong>', $description);

            // Then convert remaining markdown using Laravel's parser
            $description = \Illuminate\Mail\Markdown::parse($description);

            // Clean up any remaining double asterisks that weren't converted
            $description = preg_replace('/\*\*([^*]+)\*\*/', '$1', $description);

            return $description;
        }

        return null;
    }

    public function sendMessage()
    {
        if (empty(trim($this->question))) {
            return;
        }

        $user = Filament::auth()->user();
        $userName = $user ? $user->name : 'Guest';

        $this->messages[] = ['role' => 'user', 'content' => $this->question];

        try {
            $client = $this->getClient();
            $chat = $client->geminiPro()->startChat();

            foreach ($this->messages as $message) {
                $chat->sendMessage(new TextPart($message['content']));
            }

            $response = $chat->sendMessage(new TextPart($this->question));
            $responseText = $response->text();

            // Check if response contains "Project Title"
            $this->showCreateButton = str_contains($responseText, 'Project Title:');

            $this->messages[] = ['role' => 'assistant', 'content' => $responseText];
        } catch (\Exception $e) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'An error occurred: ' . $e->getMessage()];
        }

        $this->question = '';
        $this->dispatch('sendmessage');
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
