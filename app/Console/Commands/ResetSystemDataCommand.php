<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetSystemDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asbank:reset 
                            {--keep-client : Mantém o usuário cliente padrão carlos.silva@email.com}
                            {--force : Executa sem pedir confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera os empréstimos, parcelas e extratos do sistema ASBANK, preservando apenas os logins de acesso';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=yellow;options=bold>=========================================================</>');
        $this->line('<fg=yellow;options=bold>   ASBANK • Limpeza e Reset de Dados do Sistema          </>');
        $this->line('<fg=yellow;options=bold>=========================================================</>');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Deseja realmente zerar todos os empréstimos, parcelas e extratos do sistema?', true)) {
                $this->info('Operação cancelada pelo usuário.');
                return 0;
            }
        }

        $this->line('Iniciando limpeza de dados...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            // 1. Limpar movimentações financeiras e empréstimos
            DB::table('financial_transactions')->truncate();
            $this->info('✔ Tabela financial_transactions (Extrato) zerada.');

            DB::table('loan_installments')->truncate();
            $this->info('✔ Tabela loan_installments (Parcelas) zerada.');

            DB::table('loans')->truncate();
            $this->info('✔ Tabela loans (Empréstimos) zerada.');

            // 2. Limpar tokens de sessão e jobs
            if (DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->truncate();
            }

            // 3. Filtrar clientes e logins
            $keepClient = $this->option('keep-client');

            if ($keepClient) {
                // Mantém admin e carlos.silva
                DB::table('clients')->where('email', '!=', 'carlos.silva@email.com')->delete();
                DB::table('users')->whereNotIn('email', ['admin@asbank.com.br', 'carlos.silva@email.com'])->delete();
                $this->info('✔ Clientes e logins ajustados (mantidos: admin@asbank.com.br e carlos.silva@email.com).');
            } else {
                // Mantém admin e clientes que tenham conta ativa de login
                DB::table('clients')->whereNotIn('email', function ($q) {
                    $q->select('email')->from('users')->where('role', 'client');
                })->delete();
                $this->info('✔ Clientes mantidos apenas os vinculados aos logins cadastrados.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            $this->newLine();
            $this->info('✔ Sistema resetado com sucesso! Extratos e empréstimos zerados, logins preservados.');
            $this->newLine();

            return 0;
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            $this->error('Erro ao resetar dados: ' . $e->getMessage());
            return 1;
        }
    }
}
