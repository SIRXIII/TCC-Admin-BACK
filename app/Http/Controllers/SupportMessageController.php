<?php

namespace App\Http\Controllers;

use App\Events\SupportMessageSent;
use App\Http\Resources\SupportMessageResource;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    use ApiResponse;
    //  public function index($ticketId)
    // {
    //     $messages = SupportMessage::where('support_ticket_id', $ticketId)
    //         ->with('senderable')
    //         ->orderBy('created_at')
    //         ->get();


    //     return $this->success(SupportMessageResource::collection($messages), 'Messages retrieved successfully', 200);
    // }



    public function index($ticketId)
    {
        // Load ticket with messages + senderable for each message
        $ticket = SupportTicket::with(['messages.senderable', 'order', 'user'])
            ->findOrFail($ticketId);

        return $this->success(
            new SupportTicketResource($ticket),
            'Ticket and messages retrieved successfully',
            200
        );
    }




   public function store(Request $request)
{
    $user = auth()->user();
    // $user = auth('sanctum')->user();


    return response()->json(['user' => 1, 'user_data' => $user]);

    if (! $user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $message = SupportMessage::create([
        'support_ticket_id' => $request->ticket_id,
        'senderable_id'    => $user->id,
        'senderable_type'  => get_class($user),
        'message'          => $request->message,
    ]);

    broadcast(new SupportMessageSent($message))->toOthers();

    return $this->success($message, 'Message sent successfully', 201);
}


    public function fetchMessages($ticketId)
    {
        $messages = SupportMessage::where('support_ticket_id', $ticketId)
            ->with('senderable')
            ->orderBy('created_at', 'asc')
            ->get();

        // return response()->json($messages);
        return $this->success($messages, 'Messages retrieved successfully', 200);
    }
}
