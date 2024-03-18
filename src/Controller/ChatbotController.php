<?php

namespace App\Controller;

use App\Form\ChatbotType;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ChatbotController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    #[Route('/', name: 'app_chatbot')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ChatbotType::class);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];

            $config = new OpenAIConfig();
            $config->apiKey = $this->getParameter('openai_api_key');

            $vectorStore = new FileSystemVectorStore('../documents-vectorStore.json');
            $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator($config);

            $qa = new QuestionAnswering(
                $vectorStore,
                $embeddingGenerator,
                new OpenAIChat($config)
            );

            $answer = $qa->answerQuestion($question);

            return $this->render('chatbot/index.html.twig', [
                'controller_name' => 'ChatbotController',
                'answer' => $answer,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('chatbot/index.html.twig', [
            'controller_name' => 'ChatbotController',
            'form' => $form->createView(),
        ]);
    }
}
