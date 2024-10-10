<?php

namespace App\Http\Controllers;

use App\Http\Requests\FreshDeskRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FreshDeskController extends Controller
{
    private $freshDeskRequest;
    private $freshDeskClient;
    private $url;
    private $header;

    public function __construct(FreshDeskRequest $freshDeskRequest)
    {
        $this->freshDeskRequest = $freshDeskRequest;
        $this->freshDeskClient = new \GuzzleHttp\Client();
        $this->url = 'https://'.env('FRESHDESK_DOMAIN').'.freshdesk.com/api/v2/';
        $this->header = [env('FRESHDESK_API_KEY'), 'X'];
    }

    /**
     * Display all the tickets for a customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function showTickets(Request $request) 
    {
        $user = Auth::user();

        try {
            // Fetch the user's ID using the email
            $response = $this->freshDeskClient->request('GET', $this->url.'contacts', [
                'auth' => $this->header,
                'query' => [
                    'email' => $user->email,
                ]
            ]);

            $contact = json_decode($response->getBody(), true);
            $userId = $contact[0]['id'];

            // Fetch the tickets using the user's ID
            $response = $this->freshDeskClient->request('GET', $this->url.'tickets', [
                'auth' => $this->header,
                'query' => [
                    'requester_id' => $userId,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $result = [];

            foreach ($data as $ticket) {

                switch ($ticket['status']) {
                    case 2:
                        $ticket['status'] = 'Open';
                        break;
                    case 3:
                        $ticket['status'] = 'Pending';
                        break;
                    case 4:
                        $ticket['status'] = 'Resolved';
                        break;
                    case 5:
                        $ticket['status'] = 'Closed';
                        break;
                }

                $result[] = [
                    'ticket no' => $ticket['id'],
                    'issue' => $ticket['subject'],
                    'student name' => $ticket['custom_fields']['cf_student_name'],
                    'status' => $ticket['status'],
                    'created at' => date('M d, Y', strtotime($ticket['created_at'])),
                    'view' => 'View Ticket',
                ];
            }

            return $this->success($result, 'Freshdesk tickets', 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                Log::info($e->getResponse()->getBody());
            }

            return $this->error('Failed to fetch tickets '.$e->getMessage(), 400);
        }
    } 

    /**
     * Create a new ticket.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTicket(Request $request) 
    {
        $user = Auth::user();
        $this->freshDeskRequest->validateCreateTicket($request);
        
        try {
            $multipart = [
                [
                    'name' => 'description',
                    'contents' => $request->description
                ],
                [
                    'name' => 'subject',
                    'contents' => $request->subject
                ],
                [
                    'name' => 'email',
                    'contents' => $user->email
                ],
                [
                    'name' => 'type',
                    'contents' => $request->type
                ],
                [
                    'name' => 'priority',
                    'contents' => 1
                ],
                [
                    'name' => 'status',
                    'contents' => 2
                ],
                [
                    'name' => 'custom_fields[cf_student_name]',
                    'contents' => $request->student_name
                ],
                [
                    'name' => 'custom_fields[cf_course]',
                    'contents' => $request->course_name
                ],
            ];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $multipart[] = [
                        'name' => 'attachments[]',
                        'contents' => fopen($attachment->path(), 'r'),
                        'filename' => $attachment->getClientOriginalName()
                    ];
                }
            }

            $response = $this->freshDeskClient->request('POST', $this->url.'tickets', [
                'auth' => $this->header,
                'multipart' => $multipart
            ]);

            $data = json_decode($response->getBody(), true);
            return $this->success($data, 'Freshdesk ticket created', 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                Log::info($e->getResponse()->getBody());
            }
            return $this->error('Failed to create ticket '.$e->getMessage(), 400);
        }
    }

    /**
     * Reply to a ticket.
     *
     * @return \Illuminate\Http\Response
     */
    public function replyTicket(Request $request) 
    {
        $this->freshDeskRequest->validateReplyTicket($request);
        
        try {
            $multipart = [
                [
                    'name' => 'body',
                    'contents' => $request->description
                ],
            ];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $key => $file) {
                    $multipart[] = [
                        'name' => 'attachments[]',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName()
                    ];
                }
            }

            $response = $this->freshDeskClient->request('POST', $this->url.'tickets/'.$request->ticket_id.'/reply', [
                'auth' => $this->header,
                'multipart' => $multipart
            ]);

            $data = json_decode($response->getBody(), true);

            return $this->success($data, 'Freshdesk ticket reply', 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                Log::info($e->getResponse()->getBody());
            }

            return $this->error('Failed to reply ticket '.$e->getMessage(), 400);
        }
    }

    public function closeTicket(Request $request) 
    {
        $this->freshDeskRequest->validateTicketId($request);
    
        try {
            $response = $this->freshDeskClient->request('PUT', $this->url.'tickets/'.$request->ticket_id, [
                'auth' => $this->header,
                'json' => [
                    'status' => 5, // 5 represents the "Closed" status
                ],
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                Log::info($e->getResponse()->getBody());
            }
        }
    
        return $this->success($response->getBody(), 'Freshdesk ticket closed', 200);
    
    }

    /**
     * View a ticket.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewTicket(Request $request, $ticket_id) 
    {
        $this->freshDeskRequest->validateTicketId($ticket_id);
        $customerName = Auth::user()->name;

        try 
        {
            $response = $this->freshDeskClient
                ->request('GET', $this->url.'tickets/'.$ticket_id.'?include=conversations', 
                [ 'auth' => $this->header ]
            );

            $data = json_decode($response->getBody(), true);

            $ticketDetails = [
                'id' => $data['id'],
                'customer' => [
                    'name' => $customerName,
                    'student' => $data['custom_fields']['cf_student_name'],
                    'course' => $data['custom_fields']['cf_course'],
                    'date' => date('d/m/Y, h:i A', strtotime($data['created_at'])),
                    'subject' => $data['subject'],
                    'issue' => $data['description_text'],
                ],
                'department' => 'Customer Support',
                'status' => $data['status'] == 2 ? 'Open' : 'Closed',
                'conversations' => !empty($data['conversations']) ? array_map(function($conversation) use ($customerName) {
                    return [
                        'name' => strpos($conversation['body_text'], 'Assalam O Allaikum Brother / Sister of Islam') === 0 
                            ? (preg_match('/"(.*)"/', $conversation['from_email'], $matches) ? $matches[1] : 'Unknown')
                            : $customerName,
                        'date' => date('d/m/Y, h:i A', strtotime($conversation['created_at'])),
                        'reply' => $conversation['body_text'],
                        'role' => strpos($conversation['body_text'], 'Assalam O Allaikum Brother / Sister of Islam') === 0 
                            ? 'Agent' 
                            : 'User',
                        'attachment' => $conversation['attachments'] ?? [],
                    ];
                }, $data['conversations']) : [],
                'attachments' => $data['attachments'] ?? [],
            ];
        
            return $this->success($ticketDetails, 'Freshdesk ticket details', 200);
        } 
        catch (\GuzzleHttp\Exception\RequestException $e) 
        {
            return $this->error('Failed to fetch ticket '.$e->getMessage(), 400);
        }
    }
}
