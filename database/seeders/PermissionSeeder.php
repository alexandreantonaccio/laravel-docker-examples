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
            'grupos_permissoes.visualizar' => 'Visualizar grupos de permissões',
            'grupos_permissoes.listar' => 'Listar grupos de permissões',
            'grupos_permissoes.editar' => 'Editar grupos de permissões',
            'grupos_permissoes.excluir' => 'Excluir grupos de permissões',
            'grupos_permissoes.atribuir_usuarios' => 'Atribuir usuários a grupos',
        ];

        foreach ([
            'dominios_email' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'cargos' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'cursos' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'vinculos' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'ambientes' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'grupos_ambientes' => ['visualizar', 'listar', 'criar', 'editar', 'desativar'],
            'agendamentos.tipo' => ['criar', 'editar', 'desativar'],
            'agendamentos.docente' => ['criar', 'editar', 'desativar'],
        ] as $resource => $actions) {
            foreach ($actions as $action) {
                $permissions["{$resource}.{$action}"] = ucfirst(str_replace(['_', '.'], ' ', $resource)).": {$action}";
            }
        }

        $permissions += [
            'agendamentos.visualizar.proprio' => 'Visualizar os próprios agendamentos',
            'agendamentos.visualizar.qualquer' => 'Visualizar qualquer agendamento',
            'agendamentos.listar.proprio' => 'Listar os próprios agendamentos',
            'agendamentos.listar.qualquer' => 'Listar qualquer agendamento',
            'agendamentos.solicitar.proprio' => 'Solicitar agendamento próprio',
            'agendamentos.solicitar.qualquer' => 'Solicitar agendamento em nome de outro usuário',
            'agendamentos.fixo.criar' => 'Criar agendamento fixo ou recorrente',
            'agendamentos.editar.proprio' => 'Editar próprio agendamento',
            'agendamentos.editar.qualquer' => 'Editar qualquer agendamento',
            'agendamentos.cancelar.proprio' => 'Cancelar próprio agendamento',
            'agendamentos.cancelar.qualquer' => 'Cancelar qualquer agendamento',
            'agendamentos.aprovar.qualquer' => 'Aprovar ou rejeitar agendamentos',
            'agendamentos.configurar' => 'Configurar regras de agendamento',
        ];

        foreach ($permissions as $key => $label) {
            Permission::query()->updateOrCreate(['key' => $key], ['label' => $label]);
        }
    }
}