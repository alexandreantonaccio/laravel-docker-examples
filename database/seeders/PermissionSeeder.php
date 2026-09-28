<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'usuarios.listar' => 'Listar e pesquisar usuários',
            'usuarios.visualizar.proprio' => 'Visualizar o próprio cadastro',
            'usuarios.visualizar.qualquer' => 'Visualizar qualquer cadastro',
            'usuarios.editar.proprio' => 'Editar o próprio cadastro',
            'usuarios.editar.qualquer' => 'Editar qualquer cadastro',
            'usuarios.aprovar' => 'Aprovar ou rejeitar cadastros',
            'usuarios.gerenciar_permissoes' => 'Gerenciar permissões individuais',
            'usuarios.alterar_perfil' => 'Alterar perfil de usuário',
            'usuarios.resetar_senha' => 'Solicitar reset de senha',
            'usuarios.desativar' => 'Desativar e reativar usuários',
            'grupos_permissoes.criar' => 'Criar grupos de permissões',
            'grupos_permissoes.editar' => 'Editar grupos de permissões',
            'grupos_permissoes.excluir' => 'Excluir grupos de permissões',
            'grupos_permissoes.atribuir_usuarios' => 'Atribuir usuários a grupos',
        ];

        foreach ($permissions as $key => $label) {
            Permission::query()->updateOrCreate(['key' => $key], ['label' => $label]);
        }
    }
}