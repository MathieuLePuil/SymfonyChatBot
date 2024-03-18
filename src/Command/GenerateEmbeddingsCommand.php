<?php

namespace App\Command;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'GenerateEmbeddings',
    description: 'Add a short description for your command',
)]
class GenerateEmbeddingsCommand extends Command
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dataReader = new FileDataReader(__DIR__ . '/../../public/website.pdf');
        $documents = $dataReader->getDocuments();

        $splittedDocuments = DocumentSplitter::splitDocuments($documents, 500);

        $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator();
        $embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);

        $vectorStore = new FileSystemVectorStore();
        $vectorStore->addDocuments($embeddedDocuments);

        $io->success('Embeddings generated successfully!');

        return Command::SUCCESS;
    }
}
